<?php

namespace aura\http;

use aura\http\Uri as Uri;


abstract class Resource
{
    /**
     * HTTP method constants.
     */
    const DELETE     = 'DELETE';
    const GET        = 'GET';
    const HEAD       = 'HEAD';
    const OPTIONS    = 'OPTIONS';
    const POST       = 'POST';
    const PUT        = 'PUT';
    const TRACE      = 'TRACE';
    
    /**
     * WebDAV method constants.
     */
    const COPY       = 'COPY';
    const LOCK       = 'LOCK';
    const MKCOL      = 'MKCOL';
    const MOVE       = 'MOVE';
    const PROPFIND   = 'PROPFIND';
    const PROPPATCH  = 'PROPPATCH';
    const UNLOCK     = 'UNLOCK';
    
    /**
     * Auth constants
     */
    const BASIC      = 'BASIC';
    const DIGEST     = 'DIGEST';
    
    /**
     * Encoding constants
     */
    const GZIP       = 'GZIP';
    const DEFLATE    = 'DEFLATE';
    
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
        'method'          => self::GET,
    //$auto_set_length // todo
    //method
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
     * @var string
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

    protected $http_auth = null;
    
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
     * The URI for the request.
     * 
     * @var aura\utility\Uri
     * 
     */
    protected $resource_uri = null;
    
    /**
     * 
     * @var aura\utility\Uri
     * 
     */
    protected $uri;
    
    /**
     * 
     * @var aura\http\ResourceResponse
     * 
     */
    protected $response;
    
    protected $response_stack;

    

    public function __construct(
        Uri $uri, 
        ResourceResponse $resource_response,
//        CookieJar        $cookie_jar = null
        array $opts = array())
    {
        $this->uri               = clone $uri;
        $this->response = $resource_response;
        $this->response_stack    = new \SplStack();
        
        if ($opts) {
            $this->default_opts = array_merge($this->default_opts, $opts);
        }
        
        $this->setDefaults();
    }
    
    public function __clone()
    {
        $this->reset();
    }
    
    public function __get($key)
    {
        throw new Exception("No such property '$key'");
    }
    
    public function __set($key, $value)
    {
        throw new Exception("No such property '$key'");
    }
    
    /**
     * 
     * Reset using the constructor defaults.
     * 
     * @return aura\http\Resource
     * 
     */
    public function reset()
    {
        $this->resource_uri    = null;
        $this->auto_set_length = true;
        $this->content         = null;
        $this->headers         = array();//todo
        $this->cookies         = array();
        $this->method          = self::METHOD_GET;
        $this->referer         = null;
        $this->accept_encoding = null;
        
        $this->setDefaults();
        
        return $this;
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
     * @todo $authtype digest
     * 
     */
    public function setAuth($handle, $passwd, $authtype = self::BASIC)
    {
        if (! $handle && ! $passwd) {
            $this->http_auth = null;
            return $this;
        }
        
        if(! in_array($authtype, array(self::BASIC, self::DIGEST))) {
            
            throw new Exception("Unknown auth type '$authtype'");
        } else if (strpos($handle, ':') !== false) {
            
            throw new Exception('The handle can not contain a colon (:)');
        }
        
        $this->http_auth = array($authtype, "$handle:$passwd");
        
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
     * @todo aura\http\cookies
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
      //  $key = $this->mime_utility->headerLabel($key);//todo
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
    
    public function getHeaders()
    {
        return $this->headers;
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
            $spec = $spec->get();
        }
        $this->headers['Referer'] = $spec;
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
    
    public function getUri()
    {
        return $this->resource_uri->get(true);
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
        $this->headers['User-Agent'] = $val;
        return $this;
    }
    
    /**
     * 
     * self::GZIP|self::DEFLATE
     * 
     * @param type $enable
     * 
     * @return AbstractResource 
     * 
     */
    public function setEncoding($encoding = self::GZIP)
    {
        if ($encoding && ! function_exists('gzinflate')) {
            throw new Exception('Zlib extension is not loaded.');
        } else if (!$encoding) {
            unset($this->header['Accept-Encoding']);
            return $this;
        }
        
        $accept = array();
        
        if(self::GZIP & $encoding) {
            $accept[] = 'gzip';
        }
        
        if(self::DEFLATE & $encoding) {
            $accept[] = 'deflate';
        }
        
        $this->headers['Accept-Encoding'] = implode(',', $accept);
        
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
        if ($spec instanceof Uri) {
            $spec = $spec->get();
        }

        $this->proxy = $spec;
        
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
    
    public function setMethod($method)
    {
        $allowed = array(
            self::GET,
            self::POST,
            self::PUT,
            self::DELETE,
            self::TRACE,
            self::OPTIONS,
            self::TRACE,
            self::COPY,
            self::LOCK,
            self::MKCOL,
            self::MOVE,
            self::PROPFIND,
            self::PROPPATCH,
            self::UNLOCK
        );
        
        $method = strtoupper($this->method);
        
        if (! in_array($method, $allowed)) {
            throw new Exception_UnknownMethod($method);
        }
        
        $this->method = $method;
        
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
    public function fetch($method = null)
    {
        $allowed = array(
            self::GET,
            self::POST,
            self::PUT,
            self::DELETE,
            self::TRACE,
            self::OPTIONS,
            self::TRACE,
            self::COPY,
            self::LOCK,
            self::MKCOL,
            self::MOVE,
            self::PROPFIND,
            self::PROPPATCH,
            self::UNLOCK
        );
        
        if ($method) {
            $this->method = $method;
        }
        
        $method = strtoupper($this->method);
        
        if (! in_array($method, $allowed)) {
            throw new Exception_UnknownMethod($method);
            
        } else if (! $this->resource_uri) {
            throw new Exception('This request has no uri');
            
        }
        
        $this->prepareContent();
        
        // force the content-type header if needed
        if ($this->content_type) {
            if ($this->charset) {
                $this->content_type .= "; charset={$this->charset}";
            }
            $this->headers['Content-Type'] = $this->content_type;
        }
        
        // auto-set the content-length
        if ($this->content) {
            if ($this->auto_set_length) {
                $this->headers['Content-Length'] = strlen($this->content);
            }
            
        } else if (isset($this->headers['Content-Length'])) {
            unset($this->headers['Content-Length']);
        }
        
        // bake cookies
        if ($this->cookies) {
            $this->headers['Cookie'] = implode('; ', $this->cookies);
        }
        
        return $this->exec($this);
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
    
    protected function prepareContent()
    {
        // what kind of request is this?
        $is_get  = ($this->method == self::GET);
        $is_post = ($this->method == self::POST);
        $is_put  = ($this->method == self::PUT);
        
        // do we have any body content?
        if (is_array($this->content) && ($is_post || $is_put)) {
            // is a POST or PUT with a data array.
            // convert from array and force the content-type.
            $this->content      = http_build_query($this->content);
            $this->content_type = 'application/x-www-form-urlencoded';
            
        } else if (is_array($this->content) && $is_get) {
            // is a GET with a data array.
            // merge the content array to the cloned uri query params.
            $this->resource_uri->query = array_merge(
                $this->resource_uri->query,
                $this->content
            );
            
            // now clear out the content
            $this->content      = null;
            $this->content_type = null;
            
        } elseif (is_string($this->content)) {
            // don't do anything, honor as set by the user
            
        } else {
            // no recognizable content
            $this->content      = null;
            $this->content_type = null;
        }
    }
    
    protected function setDefaults()
    {
        $this->setCharset($this->default_opts['charset']);
        $this->setContentType($this->default_opts['content_type']);
        $this->setMaxRedirects($this->default_opts['max_redirects']);
        $this->setProxy($this->default_opts['proxy']);
        $this->setTimeout($this->default_opts['timeout']);
        $this->setUserAgent($this->default_opts['user_agent']);
        $this->setVersion($this->default_opts['version']);
        $this->setMethod($this->default_opts['method']);
        
        // set all the ssl/https options
        $this->setSslCafile($this->default_opts['ssl_cafile']);
        $this->setSslCapath($this->default_opts['ssl_capath']);
        $this->setSslLocalCert($this->default_opts['ssl_local_cert']);
        $this->setSslPassphrase($this->default_opts['ssl_passphrase']);
        $this->setSslVerifyPeer($this->default_opts['ssl_verify_peer']);
    }
}