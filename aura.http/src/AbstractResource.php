<?php

namespace aura\http;

use aura\http\Uri as Uri;
use aura\http\MimeUtility as MimeUtility;


abstract class AbstractResource implements Resource
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string charset The default character set.
     * 
     * @config string content_type The default content-type.
     * 
     * @config int max_redirects Follow no more than this many redirects.
     * 
     * @config string proxy Pass all requests through this proxy server.
     * 
     * @config int timeout Allowed connection timeout in seconds.
     * 
     * @config string user_agent The default User-Agent string.
     * 
     * @config string version The default HTTP version to use.
     * 
     * @config string ssl_cafile The local Certificate Authority file.
     * 
     * @config string ssl_capath If the CA file is not found, look in this 
     * directory for suitable CA files.
     * 
     * @config string ssl_local_cert The local certificate file.
     * 
     * @config string ssl_passphrase Passphrase to open the certificate file.
     * 
     * @config bool ssl_verify_peer Whether or not to verify the peer SSL
     * certificate.
     * 
     * @var array
     * 
     */
    protected $default_opts = array(
        'charset'         => 'utf-8',
        'content_type'    => null,
        'max_redirects'   => null,
        'proxy'           => null,
        'timeout'         => 0.0,
        'user_agent'      => null,
        'version'         => '1.1',
        'ssl_cafile'      => null,
        'ssl_capath'      => null,
        'ssl_local_cert'  => null,
        'ssl_passphrase'  => null,
        'ssl_verify_peer' => null,
    );
    
    /**
     *
     * Whether or not to automatically set the Content-Length header.
     * 
     * @var bool 
     */
    protected $auto_set_length = true; // todo

    /**
     * 
     * Content to send along with the request.
     * 
     * If an array, will be encoded with http_build_query() at fetch() time.
     * 
     * @var string|array
     * 
     */
    protected $content = null;
    
    /**
     * 
     * The URI for the request.
     * 
     * @var aura\utility\Uri
     * 
     */
    protected $resource_uri = null;
    
    /**
     * 
     * The User-Agent header value to send.
     * 
     * @var string
     * 
     */
    protected $user_agent = null;
    
    /**
     * 
     * The content-type for the body content.
     * 
     * @var string
     * 
     */
    protected $content_type = null;
    
    /**
     * 
     * The character-set for the body content.
     * 
     * @var string
     * 
     */
    protected $charset = null;
    
    /**
     * 
     * Additional headers to send with the request.
     * 
     * @var array
     * 
     */
    protected $headers = array();
    
    /**
     * 
     * Additional cookies to send with the request.
     * 
     * @var array
     * 
     */
    protected $cookies = array();
    
    /**
     * 
     * The maximum number of redirects to allow.
     * 
     * @var int
     * 
     */
    protected $max_redirects = null;
    
    /**
     * 
     * The HTTP method to use for the request (GET, POST, etc).
     * 
     * @var string
     * 
     */
    protected $method = 'GET';
    
    /**
     * 
     * Pass all HTTP requests through this proxy.
     * 
     * @var string
     * 
     */
    protected $proxy = null;
    
    /**
     * 
     * The URI this request came from (if any).
     * 
     * @var aura\utility\Uri
     * 
     */
    protected $referer = null;
    
    /**
     * 
     * Let the request time out after this many seconds.
     * 
     * @var string
     * 
     */
    protected $timeout = null;
    
    /**
     * 
     * The HTTP protocol version to use (1.0 or 1.1).
     * 
     * @var string
     * 
     */
    protected $version = '1.1';
    
    /**
     * 
     * Accept gzip and/or inflate encoding.
     * 
     * @var array
     * 
     */
    protected $accept_encoding = null;


    /**
     * 
     * Require verification of SSL certificate used?
     * 
     * @var bool
     * 
     */
    protected $ssl_verify_peer = false;
                
    /**
     * 
     * Location of Certificate Authority file on local filesystem which should
     * be used with the $ssl_verify_peer  option to authenticate the identity
     * of the remote peer.              
     * 
     * @var string
     * 
     */
    protected $ssl_cafile = null;
                
    /**
     * 
     * If $ssl_cafile is not specified or if the certificate is not
     * found there, this directory path is searched for a suitable certificate.
     * 
     * The path must be a correctly hashed certificate directory.              
     * 
     * @var string
     * 
     */
    protected $ssl_capath = null;
    
    /**
     * 
     * Path to local certificate file on filesystem. This must be a PEM encoded
     * file which contains your certificate and private key. It can optionally
     * contain the certificate chain of issuers.              
     * 
     * @var string
     * 
     */
    protected $ssl_local_cert = null;
    
    /**
     * 
     * Passphrase with which the $ssl_local_cert file was encoded.
     * 
     * @var string
     * 
     */
    protected $ssl_passphrase = null;
    
    /**
     * 
     * @var aura\http\MimeUtility
     * 
     */
    protected $mime_utility;
    
    /**
     * 
     * @var aura\utility\Uri
     * 
     */
    protected $uri;



    public function __construct(Uri $uri, MimeUtility $mime_utility, array $opts = array())
    {
        $this->uri          = clone $uri;
        $this->mime_utility = $mime_utility;
        
        if ($opts) {
            $this->default_opts = array_merge($this->default_opts, $opts);
        }
        
        $this->setDefaults();
    }
    
    public function __clone()
    {
        $this->reset();
    }
    
    /**
     * 
     * Reset using constructor defaults.
     * 
     * @return aura\http\Resource
     * 
     */
    public function reset()
    {
        $this->resource_uri    = null;
        $this->auto_set_length = true;
        $this->content         = null;
        $this->headers         = array();
        $this->cookies         = array();
        $this->method          = self::METHOD_GET;
        $this->referer         = null;
        $this->accept_encoding = null;
        
        $this->setDefaults();
        
        return $this;
    }
    
    /**
     * 
     * Returns all options as an array.
     * 
     * @return array
     * 
     */
    public function getOptions()
    {
        $list = array(
            'accept_encoding',
            'set_auto_length',
            'charset',
            'content_type',
            'cookies',
            'headers',
            'max_redirects',
            'method',
            'proxy',
            'timeout',
            'resource_uri',
            'user_agent',
            'version',
            'ssl_cafile',
            'ssl_capath',
            'ssl_local_cert',
            'ssl_passphrase',
            'ssl_verify_peer',
        );
        
        $opts = array();
        foreach ($list as $item) {
            $opts[$item] = $this->$item;
        }
        
        return $opts;
    }
    
    /**
     * 
     * Sets "Basic" authorization credentials.
     * 
     * Note that username handles may not have ':' in them.
     * 
     * If both the handle and password are empty, turns off authorization.
     * 
     * @param string $handle The username or login name.
     * 
     * @param string $passwd The associated password for the handle.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     * @throws \aura\http\Exception If the handle contains ':'.
     * 
     * @todo digest
     * 
     */
    public function setBasicAuth($handle, $passwd)
    {
        // turn off authorization?
        if (! $handle && ! $passwd) {
            unset($this->headers['Authorization']);
            return $this;
        }
        
        // is the handle allowed?
        if (strpos($handle, ':') !== false) {
            throw new Exception('The handle can not contain a colon (:)');
        }
        
        // set authorization header
        $value = 'Basic ' . base64_encode("$handle:$passwd");
        $this->headers['Authorization'] = $value;
        
        // done
        return $this;
    }
    
    /**
     * 
     * Sets the character set for the body content.
     * 
     * @param string $val The character set, e.g. "utf-8".
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setCharset($val)
    {
        $this->charset = $val;
        return $this;
    }
    
    /**
     * 
     * Sets the body content; technically you can use the public $content 
     * property, but this allows method-chaining.
     * 
     * If you pass an array, the prepare() method will automatically call
     * http_build_query() on the array and set the content-type for you.
     * 
     * @param string|array $val The body content.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setContent($val)
    {
        $this->content = $val;
        return $this;
    }
    
    /**
     * 
     * Sets the content-type for the body content.
     * 
     * @param string $val The content-type, e.g. "text/plain".
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setContentType($val)
    {
        $this->content_type = $val;
        return $this;
    }
    
    /**
     * 
     * Sets a cookie value in $this->cookies to add to the request.
     * 
     * @param string $name The name of the cookie.
     * 
     * @param string|array $spec If a string, the value of the cookie; if an
     * array, uses the 'value' key for the cookie value.  Either way, the 
     * value will be URL-encoded at fetch() time.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setCookie($name, $spec = null)
    {
        if (is_scalar($spec)) {
            $value = (string) $spec;
        } else {
            $value = $spec['value'];
        }
        
        $name = str_replace(array("\r", "\n"), '', $name);
        $this->cookies[$name] = $value;
        return $this;
    }
    
    /**
     * 
     * Sets multiple cookie values in $this->cookies to add to the request.
     * 
     * @param array $cookies An array of key-value pairs where the key is the
     * cookie name and the value is the cookie value.  The values will be
     * URL-encoded at fetch() time.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setCookies($cookies)
    {
        foreach ($cookies as $name => $spec) {
            $this->setCookie($name, $spec);
        }
        return $this;
    }
    
    /**
     * 
     * Sets a header value in $this->headers for sending at fetch() time.
     * 
     * This method will not set cookie values; use setCookie() or setCookies()
     * instead.
     * 
     * @param string $key The header label, such as "X-Foo-Bar".
     * 
     * @param string $val The value for the header.  When null or false,
     * deletes the header.
     * 
     * @param bool $replace This header value should replace any previous
     * values of the same key.  When false, the same header key is sent
     * multiple times with the different values.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     * @see [[php::header() | ]]
     * 
     * @throws aura\http\Exception Cannot use setHeader to set cookies.
     * 
     */
    public function setHeader($key, $val, $replace = true)
    {
        // normalize the header key and keep a lower-case version
        $key = $this->mime_utility->headerLabel($key);
        $low = strtolower($key);
        
        // use special methods when available
        $special = array(
            'content-type'  => 'setContentType',
            'http'          => 'setVersion',
            'referer'       => 'setReferer',
            'user-agent'    => 'setUserAgent',
        );
        
        if (! empty($special[$low])) {
            $method = $special[$low];
            return $this->$method($val);
        }
        
        // don't allow setting of cookies
        if ($low == 'cookie') {
            throw new Exception('Use setCookie() instead.');
        }
        
        // how to add the header?
        if ($val === null || $val === false) {
            // delete the key
            unset($this->headers[$key]);
        } elseif ($replace || empty($this->headers[$key])) {
            // replacement, or first instance of the key
            $this->headers[$key] = $val;
        } else {
            // second or later instance of the key
            settype($this->headers[$key], 'array');
            $this->headers[$key][] = $val;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * When making the request, allow no more than this many redirects.
     * 
     * @param int $max The max number of redirects to allow.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setMaxRedirects($max)
    {
        if ($max === null) {
            $this->max_redirects = null;
        } else {
            $this->max_redirects = (int) $max;
        }
        return $this;
    }
    
    /**
     * 
     * Sets the HTTP method for the request (GET, POST, etc).
     * 
     * Recgonized methods are OPTIONS, GET, HEAD, POST, PUT, DELETE,
     * TRACE, and CONNECT, GET, POST, PUT, DELETE, TRACE, OPTIONS, COPY,
     * LOCK, MKCOL, MOVE, PROPFIND, PROPPATCH AND UNLOCK.
     * 
     * @param string $method The method to use for the request.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     * @throws aura\http\Exception_UnknownMethod
     * 
     */
    public function setMethod($method)
    {
        $allowed = array(
            self::METHOD_GET,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_DELETE,
            self::METHOD_TRACE,
            self::METHOD_OPTIONS,
            self::METHOD_TRACE,
            self::METHOD_COPY,
            self::METHOD_LOCK,
            self::METHOD_MKCOL,
            self::METHOD_MOVE,
            self::METHOD_PROPFIND,
            self::METHOD_PROPPATCH,
            self::METHOD_UNLOCK
        );
        
        $method = strtoupper($method);
        
        if (! in_array($method, $allowed)) {
            throw new Exception_UnknownMethod($method);
        }
        
        $this->method = $method;
        
        // done
        return $this;
    }
    
    /**
     * 
     * Send all requests through this proxy server.
     * 
     * @param string|Uri $spec The URI for the proxy server.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setProxy($spec)
    {
        if (null === $spec) {
            $proxy = null;
        } else if ($spec instanceof Uri) {
            $this->proxy = $spec;
        } else {
            $this->proxy = clone $this->uri;
            $this->proxy->set($spec);
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Sets the referer for the request.
     * 
     * @param Uri|string $spec The referer URI.
     * 
     * @return aura\http\Resource This adapater object.
     * 
     */
    public function setReferer($spec)
    {
        if ($spec instanceof Uri) {
            $this->referer = $spec;
        } else {
            $this->referer = clone $this->uri;
            $this->referer->set($spec);
        }
        return $this;
    }
    
    /**
     * 
     * Sets the request timeout in seconds.
     * 
     * @param float $time The timeout in seconds.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setTimeout($time)
    {
        $this->timeout = (float) $time;
        return $this;
    }
    
    /**
     * 
     * Sets the URI for the request.
     * 
     * @param Uri|string $spec The URI for the request.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setUri($spec)
    {
        if ($spec instanceof Uri) {
            $this->resource_uri = $spec;
        } else {
            $this->resource_uri = clone $this->uri;
            $this->resource_uri->set($spec);
        }
        
        return $this;
    }
    
    /**
     * 
     * Sets the User-Agent for the request.
     * 
     * @param string $val The User-Agent value.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setUserAgent($val)
    {
        $this->user_agent = $val;
        return $this;
    }
    
    /**
     * 
     * Sets the HTTP protocol version for the request (1.0 or 1.1).
     * 
     * @param string $version The version number (1.0 or 1.1).
     * 
     * @return aura\http\Resource This adapter object.
     * 
     * @throws aura\http\Exception_UnknownVersion
     * 
     */
    public function setVersion($version)
    {
        if ($version != '1.0' && $version != '1.1') {
            throw new Exception_UnknownVersion($version);
        }
        $this->version = $version;
        return $this;
    }
    
    /**
     * 
     * @param type $enable
     * 
     * @return AbstractResource 
     * 
     */
    public function setGzip($enable = true)
    {
        if ($enable) {
            if (! function_exists('gzinflate')) {
                throw new Exception('Zlib extension is not loaded.');
            }
            $this->accept_encoding['gzip'] = 'gzip';
        } else {
            unset($this->accept_encoding['gzip']);
        }
        
        return $this;
    }
    
    /**
     *
     * @param type $enable
     * 
     * @return AbstractResource 
     * 
     */
    public function setDeflate($enable = true)
    {
        if ($enable) {
            if (! function_exists('gzinflate')) {
                throw new Exception('Zlib extension is not loaded.');
            }
            $this->accept_encoding['deflate'] = 'deflate';
        } else {
            unset($this->accept_encoding['deflate']);
        }
        
        return $this;
    }
    
    /**
     * 
     * Require verification of SSL certificate used?
     * 
     * @param bool $flag True or false.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setSslVerifyPeer($flag)
    {
        $this->ssl_verify_peer = (bool) $flag;
        return $this;
    }
    
    /**
     * 
     * Location of Certificate Authority file on local filesystem which should
     * be used with the $ssl_verify_peer option to authenticate the identity
     * of the remote peer.              
     * 
     * @param string $val The CA file.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setSslCafile($val)
    {
        $this->ssl_cafile = $val;
        return $this;
    }
    
    /**
     * 
     * If $ssl_cafile is not specified or if the certificate is not
     * found there, this directory path is searched for a suitable certificate.
     * 
     * The path must be a correctly hashed certificate directory.              
     * 
     * @param string $val The CA path.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setSslCapath($val)
    {
        $this->ssl_capath = $val;
        return $this;
    }
    
    /**
     * 
     * Path to local certificate file on filesystem. This must be a PEM encoded
     * file which contains your certificate and private key. It can optionally
     * contain the certificate chain of issuers.              
     * 
     * @param string $val The local certificate file path.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setSslLocalCert($val)
    {
        $this->ssl_local_cert = $val;
        return $this;
    }
    
    /**
     * 
     * Passphrase with which the $ssl_local_cert file was encoded.
     * 
     * @param string $val The passphrase.
     * 
     * @return aura\http\Resource This adapter object.
     * 
     */
    public function setSslPassphrase($val)
    {
        $this->ssl_passphrase = $val;
        return $this;
    }
    
    /**
     * 
     * Fetches all aura\http\Resource objects from the specified URI (this
     * includes all intervening redirects).
     * 
     * @return aura\http\Resource
     * 
     */
    public function fetch()
    {
        // get prepared headers and content for the request
        list($req_uri, $req_headers, $req_content) = $this->prepareRequest();
        
        // fetch the headers and content from the response
        $req_loc = $req_uri->get(true);
        list($headers, $content) = $this->adapterFetch($req_loc, $req_headers,
            $req_content);
        
        // a stack of responses; this is because there may have been redirects,
        // etc.
        $response              = new \SplStack();
        $resource_response_obj = new ResourceResponse($this->mime_utility);
        $resource_response     = null;
        
        // add headers for each response
        foreach ($headers as $header) {
            
            // not an HTTP header, must be a "real" header for the current
            // response number.  split on the first colon.
            $pos     = strpos($header, ':');
            $is_http = strtoupper(substr($header, 0, 5)) == 'HTTP/';
            
            // look for an HTTP header to start a new response object.
            if ($pos === false && $is_http) {
                
                if ($resource_response) {
                    $response->push($resource_response);
                }
                
                $resource_response = clone $resource_response_obj;
                
                // set the version, status code, and status text in the response
                preg_match('/HTTP\/(.+?) ([0-9]+)(.*)/i', $header, $matches);
                $resource_response->setVersion($matches[1]);
                $resource_response->setStatusCode($matches[2]);
                $resource_response->setStatusText($matches[3]);
                
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
                $resource_response->parseAndSetCookie($value);
            } elseif ($label) {
                // set the header, allow multiples
                $resource_response->setHeader($label, $value, false);
            }
        }
        
        if ($resource_response) {
            // set the content on the last response
            $resource_response->content = $content;
            $response->push($resource_response);
        } else {
            throw new Exception_EmptyResponse();
        }
        
        return $response;
    }
    
    /**
     * 
     * Fetches from the specified URI and returns the response message as a
     * string.
     * 
     * @return string
     * 
     */
    public function fetchRaw()
    {
        // get prepared headers and content for the request
        list($req_uri, $req_headers, $req_content) = $this->prepareRequest();
        
        // fetch the headers and content from the response
        $req_loc = $req_uri->get(true);
        list($headers, $content) = $this->adapterFetch($req_loc, $req_headers,
            $req_content);
        
        // return the raw message
        return implode("\r\n", $headers)
             . "\r\n\r\n"
             . $content;
    }
    
    /**
     * 
     * Prepares $this->headers, $this->cookies, and $this->content for the
     * request.
     * 
     * @return array A sequential array where element 0 is a URI object,
     * element 1 is string of headers (including cookies), and element 2 is a 
     * string of content.
     * 
     */
    protected function prepareRequest()
    {
        // get the URI
        if (! $this->resource_uri) {
            throw new Exception('This requset has no uri');
        } else {
            $uri = $this->resource_uri;
        }
        
        // what kind of request is this?
        $is_get  = ($this->method == self::METHOD_GET);
        $is_post = ($this->method == self::METHOD_POST);
        $is_put  = ($this->method == self::METHOD_PUT);
        
        // do we have any body content?
        if (is_array($this->content) && ($is_post || $is_put)) {
            
            // is a POST or PUT with a data array.
            // convert from array and force the content-type.
            $content      = http_build_query($this->content);
            $content_type = 'application/x-www-form-urlencoded';
            
        } elseif (is_array($this->content) && $is_get) {
            
            // is a GET with a data array.
            // merge the content array to the cloned uri query params.
            $uri->query = array_merge(
                $uri->query,
                $this->content
            );
            
            // now clear out the content
            $content      = null;
            $content_type = null;
            
        } elseif (is_string($this->content)) {
            
            // honor as set by the user
            $content      = $this->content;
            $content_type = $this->content_type;
            
        } else {
            
            // no recognizable content
            $content      = null;
            $content_type = null;
            
        }
        
        // get a list of the headers as they are now
        $list = $this->headers;
        
        if ($this->accept_encoding) {
            $encoding = implode(', ', $this->accept_encoding);
            $list['Accept-Encoding'] = $encoding;
        }
        
        // force the content-type header if needed
        if ($content_type) {
            if ($this->charset) {
                $content_type .= "; charset={$this->charset}";
            }
            $list['Content-Type'] = $content_type;
        }
        
        // auto-set the content-length
        if ($this->auto_set_length) {
            if ($content) {
                $list['Content-Length'] = strlen($content);
            } else {
                unset($list['Content-Length']);
            }
        }
        
        // force the user-agent header if needed
        if ($this->user_agent) {
            $list['User-Agent'] = $this->user_agent;
        }
        
        // force the referer if needed
        if ($this->referer) {
            $list['Referer'] = $this->referer->get(true);
        }
        
        // convert the list of all header values to a sequential array
        $headers = array();
        foreach ($list as $key => $set) {
            settype($set, 'array');
            foreach ($set as $val) {
                $headers[] = $this->mime_utility->headerLine($key, $val);
            }
        }
        
        // create additional cookies in the headers array
        if ($this->cookies) {
            $val = array();
            foreach ($this->cookies as $name => $data) {
                $val[] = "$name=" . urlencode($data);
            }
            $headers[] = $this->mime_utility->headerLine('Cookie', implode(';', $val));
        }
        
        // done!
        return array($uri, $headers, $content);
    }

    /**
     * 
     * Support method to make the request, then return headers and content.
     * 
     * @param string $uri The URI get a response from.
     * 
     * @param array $headers A sequential array of header lines for the request.
     * 
     * @param string $content A string of content for the request.
     * 
     * @return array A sequential array where element 0 is a sequential array of
     * header lines, and element 1 is the body content.
     * 
     */
    abstract protected function adapterFetch($uri, $headers, $content);
    
    protected function setDefaults()
    {
        $this->setCharset($this->default_opts['charset']);
        $this->setContentType($this->default_opts['content_type']);
        $this->setMaxRedirects($this->default_opts['max_redirects']);
        $this->setProxy($this->default_opts['proxy']);
        $this->setTimeout($this->default_opts['timeout']);
        $this->setUserAgent($this->default_opts['user_agent']);
        $this->setVersion($this->default_opts['version']);
        
        // set all the ssl/https options
        $this->setSslCafile($this->default_opts['ssl_cafile']);
        $this->setSslCapath($this->default_opts['ssl_capath']);
        $this->setSslLocalCert($this->default_opts['ssl_local_cert']);
        $this->setSslPassphrase($this->default_opts['ssl_passphrase']);
        $this->setSslVerifyPeer($this->default_opts['ssl_verify_peer']);
    }
}