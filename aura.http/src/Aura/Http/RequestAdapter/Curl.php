<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\RequestAdapter;
use Aura\Http\Request as Request;

/**
 * 
 * Curl request adapter for the Aura Request library.
 * 
 * @package aura.http
 * 
 */
class Curl implements \Aura\Http\RequestAdapter
{
    /**
     * 
     * Curl resource
     * 
     * @var resource
     * 
     */
    protected $ch;
    
    /**
     * 
     * Is the request over ssl.
     * 
     * @var bool
     * 
     */
    protected $is_secure;
    
    /**
     * 
     * @var Aura\Http\RequestResponse
     * 
     */
    protected $response;
    
    /**
     * 
     * @var \SplStack
     * 
     */
    protected $response_stack;
    
    /**
     * 
     * File resource.
     * 
     * @var resource|string
     * 
     */
    protected $file_handle = null;
    
    protected $file;

    /**
     * 
     * The request content.
     * 
     * @var string
     * 
     */
    protected $content = '';
    

    protected $curl_opts;

    
    /**
     * 
     * Throws an exception if the curl extension isn't loaded.
     * 
     * @param \Aura\Http\RequestResponse $response
     * 
     * @param array $curl_opts
     * 
     * @throws Aura\Http\Exception If Curl extension is not loaded.
     * 
     */
    public function __construct(
        \Aura\Http\RequestResponse $response,
        array $curl_opts = array()
        )
    {
        if (! extension_loaded('curl')) {
            throw new Exception('Curl extension is not loaded.');
        }
        
        $this->curl_opts = $curl_opts;
        $this->response  = $response;
    }
    
    /**
     * 
     * Initialize the connection.
     * 
     * @param string $url
     * 
     * @throws Exception\ConnectionFailed
     * 
     */
    public function connect($url)
    {
        $this->response_stack = new \SplStack();
        $this->ch             = curl_init($url);
        
        if (false === $this->ch) {
            throw new Exception\ConnectionFailed(
                'Connection failed: ('. curl_errno($this->ch) . ' ) ' .
                curl_error($this->ch));
        }
        
        $this->is_secure = strtolower(substr($url, 0, 5)) == 'https' ||
                           strtolower(substr($url, 0, 3)) == 'ssl';

        // set the curl options provided through the constructor.
        foreach ($this->curl_opts as $opt => $value) {
            curl_setopt($this->ch, $opt, $value);
        }
    }
    
    /**
     * 
     * The request options.
     * 
     * @param \ArrayObject $options
     * 
     */
    public function setOptions(\ArrayObject $options)
    {
        
        if (! empty($options->cookiejar)) {
            curl_setopt($this->ch, CURLOPT_COOKIEJAR,  $options->cookiejar);
            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $options->cookiejar);
        }

        // automatically set the Referer: field in requests where it
        // follows a Location: redirect.
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
        
        // follow any "Location: " header that the server sends as
        // part of the HTTP header (note this is recursive, PHP will follow
        // as many "Location: " headers that it is sent, unless
        // CURLOPT_MAXREDIRS is set).
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        
        // http basic or digest auth
        if (!empty($options->http_auth)) {
            $auth_types = array(
                Request::BASIC  => CURLAUTH_BASIC,
                Request::DIGEST => CURLAUTH_DIGEST
            );

            curl_setopt($this->ch, CURLOPT_HTTPAUTH, $options->http_auth[0]);
            curl_setopt($this->ch, CURLOPT_USERPWD,  $options->http_auth[1]);
        }

        // property-name => curlopt-constant
        $var_opt = array(
            'proxy'         => CURLOPT_PROXY,
            'max_redirects' => CURLOPT_MAXREDIRS,
            'timeout'       => CURLOPT_TIMEOUT,
        );
        
        // set other behaviours
        foreach ($var_opt as $var => $opt) {
            // use this comparison so boolean false and integer zero values
            // are honored
            if ($options->$var !== null) {
                curl_setopt($this->ch, $opt, $options->$var);
            }
        }
        
        // ssl
        
        if ($this->is_secure) {
            // property-name => curlopt-constant
            $var_opt = array(
                'ssl_verify_peer' => CURLOPT_SSL_VERIFYPEER,
                'ssl_cafile'      => CURLOPT_CAINFO,
                'ssl_capath'      => CURLOPT_CAPATH,
                'ssl_local_cert'  => CURLOPT_SSLCERT,
                'ssl_passphrase'  => CURLOPT_SSLCERTPASSWD,
            );
            
            // set other behaviors
            foreach ($var_opt as $var => $opt) {
                // use this comparison so boolean false and integer zero
                // values are honored
                if ($options->$var !== null) {
                    curl_setopt($this->ch, $opt, $options->$var);
                }
            }
        }
        
        // output
        
        // don't include the headers in the response
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        
        // return the transfer as a string instead of printing it
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($options->file) {
            $this->file        = $options->file;
            $this->file_handle = fopen($options->file, 'w'); // todo errors | is dir
        }
    
        curl_setopt($this->ch, CURLOPT_WRITEFUNCTION,  array($this, 'saveContent'));
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'saveHeaders'));
    }
    
    /**
     * 
     * Execute the request.
     * 
     * @param string $method
     * 
     * @param string $version
     * 
     * @param array $headers
     * 
     * @param string $content
     * 
     * @return \SplStack
     * 
     * @throws Exception\ConnectionFailed
     * 
     * @throws Exception\EmptyResponse
     * 
     */
    public function exec($method, $version, array $headers, $content)
    {

        // only send content if we're POST or PUT
        $send_content = $method == Request::POST
                     || $method == Request::PUT;
        
        if ($send_content) {//} && ! empty($content)) {
            if (is_array($content) && 
                false === strpos($headers['Content-Type'], 'multipart/form-data')) {
                // content does not contain any files
                $content = http_build_query($content);
            }

            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $content);

        }
        

        $this->setMethod($method);
        $this->setHeaders($headers);
    
        $response = curl_exec($this->ch);
        
        if (null === $response) {
            throw new Exception\ConnectionFailed(
                'Connection failed: ('. curl_errno($this->ch) . ' ) ' .
                curl_error($this->ch));
        }
        
        curl_close($this->ch);
        
        if ($this->file_handle) {
            fclose($this->file_handle);
        }
        
        $this->ch = $this->file_handle = null;
        
        if ($this->response_stack->isEmpty()) {
            throw new Exception\EmptyResponse('The server did not return a response.');
        }
        
        return $this->response_stack;
    }
    
    /**
     * 
     * Set the HTTP version.
     *
     * @param string $version
     *
     */
    protected function setVersion($version)
    {
        switch ($version) 
        {
            case '1.0':
                curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                break;
                
            case '1.1':
                curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                break;
                
            default:
                // let curl decide
                curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_NONE);
                break;
        }
    }

    /**
     * 
     * Set the HTTP request method.
     * 
     * @param string $method
     *
     */
    protected function setMethod($method)
    {
        switch ($method)
        {
            case 'GET':
                curl_setopt($this->ch, CURLOPT_HTTPGET, true);
                break;
                
            case Request::POST:
                curl_setopt($this->ch, CURLOPT_POST, true);
                break;
                
            case 'PUT':
                curl_setopt($this->ch, CURLOPT_PUT, true);
                break;
                
            case 'HEAD':
                curl_setopt($this->ch, CURLOPT_HEAD, true);
                break;
                
            default:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }
    }
    
    /**
     * 
     * Set the Headers.
     * 
     * @param array $headers
     *
     */
    protected function setHeaders(array $headers)
    {
        // set specialized headers and retain all others
        if (isset($headers['Cookie'])) {
            curl_setopt($this->ch, CURLOPT_COOKIE, $headers['Cookie']);
            unset($headers['Cookie']);
        }
        
        if (isset($headers['User-Agent'])) {
            curl_setopt($this->ch, CURLOPT_USERAGENT, $headers['User-Agent']);
            unset($headers['User-Agent']);
        }
        
        if (isset($headers['Referer'])) {
            curl_setopt($this->ch, CURLOPT_REFERER, $headers['Referer']);
            unset($headers['Referer']);
        }
        
        // all remaining headers
        if ($headers) {
            $prepared_headers = array();
            foreach ($headers as $key => $set) {
                settype($set, 'array');
                foreach ($set as $val) {
                    $prepared_headers[] = "{$key}: {$val}";//Solar_Mime::headerLine($key, $val);
                }
            }
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $prepared_headers);
        }
    }
    
    /**
     * 
     * Callback method for CURLOPT_WRITEFUNCTION.
     * 
     * @param resource $ch
     * 
     * @param string $content
     * 
     */
    protected function saveContent($ch, $content)
    {
        if($this->file_handle) {
            
            $is_resource = is_resource($this->file_handle);
            
            // file_handle is not a resource and is not empty try and extract
            // a filename from the headers else generate a name then
            // open a file resource.
            if(! $is_resource) {
                $filename = 'content.' . microtime() . '.out';
                        
                if (isset($this->response->header->{'Content-Disposition'})) {
                    
                    $filename = $this->response->header->{'Content-Disposition'};
                    preg_match('/filename=[\'|"]([^\'"]*)/', $filename, $m);
                    
                    if (! empty($m[1])) {
                        $filename = basename($m[1]);
                    }
                }
                
                $filename          = $this->file_handle . 
                                     DIRECTORY_SEPARATOR . $filename;
                $this->file_handle = fopen($filename, 'w');
            }
            
            $this->response->setContent($this->file, false, true);
            fwrite($this->file_handle, $content);// todo errors
            
        } else {
            $this->response->setContent($content);
        }
        
        return strlen($content);
    }
    
    /**
     * 
     * Callback method for CURLOPT_HEADERFUNCTION.
     * 
     * @param resource $ch
     * 
     * @param string $header
     * 
     */
    protected function saveHeaders($ch, $header)
    { 
        $length = strlen($header);
        
        // remove line endings
        $header = trim($header);
        
        // blank header (double line endings)
        if (! $header) {
            return $length;
        }
        
        // not an HTTP header, must be a "real" header for the current
        // response number.  split on the first colon.
        $pos     = strpos($header, ':');
        $is_http = strtoupper(substr($header, 0, 5)) == 'HTTP/';
        
        // look for an HTTP header to start a new response object.
        if ($pos === false && $is_http) {
            
            $this->response = clone $this->response;
            $this->response_stack->push($this->response);
            
            // set the version, status code, and status text in the response
            preg_match('/HTTP\/(.+?) ([0-9]+)(.*)/i', $header, $matches);
            $this->response->setVersion($matches[1]);
            $this->response->setStatusCode($matches[2]);
            $this->response->setStatusText($matches[3]);
            
            // go to the next header line
            return $length;
        }
        
        // the header label is before the colon
        $label = substr($header, 0, $pos);
        
        // the header value is the part after the colon,
        // less any leading spaces.
        $value = ltrim(substr($header, $pos+1));
        
        // is this a set-cookie header?
        if (strtolower($label) == 'set-cookie') {
            
            $this->response->cookies->setFromString($value);
        } elseif ($label) {
            // set the header, allow multiples
            $this->response->headers->add($label, $value);
        }
        
        return $length;
    }
}