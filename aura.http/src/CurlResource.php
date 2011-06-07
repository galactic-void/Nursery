<?php

namespace aura\http;

use aura\http\Uri as Uri;


class CurlResource extends Resource
{
    
    
    /**
     * 
     * Throws an exception if the curl extension isn't loaded
     * 
     * @return void
     * 
     * @throws aura\http\Exception If Curl extension is not loaded.
     * 
     * @author Bahtiar Gadimov <bahtiar@gadimov.de>
     * 
     */
    public function __construct(
        Uri $uri, 
        ResourceResponse $resource_response,
//        CookieJar        $cookie_jar = null
        array $opts = array())
    {
        if (! extension_loaded('curl')) {
            throw new Exception('Curl extension is not loaded.');
        }
        
        parent::__construct($uri, $resource_response, $opts);
        /*
        $this->resource_response = $resource_response;
  //      $this->cookie_jar        = $cookie_jar;
        $this->response_stack    = new \SplStack();
        */
    }

    /**
     * 
     * 
     * @todo Implement an exception for timeouts.
     * 
     */
    public function exec()
    {
      //  $this->request = $request;
        // prepare the connection and get the response
        $ch       = $this->setUp();
        $response = curl_exec($ch);
        
        // did we hit any errors?
        if ($response === false || $response === null) {
            throw new Exception_ConnectionFailed(curl_errno($ch), curl_error($ch));
        }
        
        // get the metadata and close the connection
        $meta = curl_getinfo($ch);
        
        // get the header lines from the response
        $headers = explode(
            "\r\n",
            substr($response, 0, $meta['header_size'])
        );
        
        // get the content portion from the response
        $content = substr($response, $meta['header_size']);
        
        curl_close($ch);
        
        $this->saveResponse($headers, $content);
        
        if ($this->response_stack->isEmpty()) {
            throw new Exception_EmptyResponse();
        }
        
        return $this->response_stack;
    }
    
    /**
     * 
     * Builds a cURL resource handle for _fetch() from property options.
     * 
     * @param Solar_Uri $uri The URI get a response from.
     * 
     * @param array $headers A sequential array of headers.
     * 
     * @param string $content The body content.
     * 
     * @return resource The cURL resource handle.
     * 
     * @see <http://php.net/curl>
     * 
     * @todo HTTP Authentication
     * 
     */
    protected function setUp()
    {
        $ch = curl_init($this->resource_uri->get(true));
        
        //curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
        
        // Request method
        switch ($this->method)
        {
            case Resource::GET:
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
                
            case Resource::POST:
                curl_setopt($ch, CURLOPT_POST, true);
                break;
                
            case Resource::PUT:
                curl_setopt($ch, CURLOPT_PUT, true);
                break;
                
            case Resource::HEAD:
                curl_setopt($ch, CURLOPT_HEAD, true);
                break;
                
            default:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 
                    $this->method);
                break;
        }
        
        // Headers
        
        // HTTP version
        switch ($this->version) 
        {
            case '1.0':
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                break;
                
            case '1.1':
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                break;
                
            default:
                // let curl decide
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_NONE);
                break;
        }
        
        // http auth basic & digest
        if ($this->http_auth) {
            
            $type = array(
                self::BASIC  => CURLAUTH_BASIC,
                self::DIGEST => CURLAUTH_DIGEST
            );
            
            curl_setopt($ch, CURLOPT_HTTPAUTH, $type[$this->http_auth[0]]);
            curl_setopt($ch, CURLOPT_USERPWD,  $this->http_auth[1]);
        }
        
        // set specialized headers and retain all others
        if (isset($this->headers['Cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->headers['Cookie']);
            unset($this->headers['Cookie']);
        }
        
        if (isset($this->headers['User-Agent'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->headers['User-Agent']);
            unset($this->headers['User-Agent']);
        }
        
        if (isset($this->headers['Referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $this->headers['Referer']);
            unset($this->headers['Referer']);
        }
        
        // all remaining headers
        if ($this->headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }
        
        // Content
        
        // only send content if we're POST or PUT
        $send_content = $this->method == Resource::POST
                     || $this->method == Resource::PUT;
        
        if ($send_content && ! empty($this->content)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->content);
        }
        
        // cUrl
        
        // todo cookiejar stream
        
        // convert Unix newlines to CRLF newlines on transfers.
        curl_setopt($ch, CURLOPT_CRLF, true);
        
        // automatically set the Referer: field in requests where it
        // follows a Location: redirect.
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        
        // follow any "Location: " header that the server sends as
        // part of the HTTP header (note this is recursive, PHP will follow
        // as many "Location: " headers that it is sent, unless
        // CURLOPT_MAXREDIRS is set).
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // include the headers in the response
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        // return the transfer as a string instead of printing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // property-name => curlopt-constant
        $var_opt = array(
            'proxy'         => CURLOPT_PROXY,
            'max_redirects' => CURLOPT_MAXREDIRS,
            'timeout'       => CURLOPT_TIMEOUT,
        );
        
        // set other behaviors
        foreach ($var_opt as $var => $opt) {
            // use this comparison so boolean false and integer zero values
            // are honored
            if ($this->$var !== null) {
                curl_setopt($ch, $opt, $this->$var);
            }
        }
        
        // ssl
        
        $is_secure = strtolower(substr($this->uri, 0, 5)) == 'https' ||
                     strtolower(substr($this->uri, 0, 3)) == 'ssl';
        
        if ($is_secure) {
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
                if ($this->$var !== null) {
                    curl_setopt($ch, $opt, $this->$var);
                }
            }
        }
        
        return $ch;
    }
    
    protected function saveResponse(array $headers, $content)
    {
        
        foreach ($headers as $header) {
            
            // not an HTTP header, must be a "real" header for the current
            // response number.  split on the first colon.
            $pos     = strpos($header, ':');
            $is_http = strtoupper(substr($header, 0, 5)) == 'HTTP/';
            
            // look for an HTTP header to start a new response object.
            if ($pos === false && $is_http) {
                
                $this->response = clone $this->response;
                $this->response_stack->push($this->response);//todo check me
                
                // set the version, status code, and status text in the response
                preg_match('/HTTP\/(.+?) ([0-9]+)(.*)/i', $header, $matches);
                $this->response->setVersion($matches[1]);
                $this->response->setStatusCode($matches[2]);
                $this->response->setStatusText($matches[3]);
                
                // go to the next header line
                continue;
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
        }
        
        $this->response->setContent($content);
    }
}