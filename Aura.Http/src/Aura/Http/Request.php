<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

use Aura\Uri\Http as HttpUri;

/**
 * 
 * HTTP Request library.
 * 
 * @package Aura.Http
 * 
 */
class Request
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
     * @config string method The default HTTP method.
     * 
     * @var array
     * 
     */
    protected $default_opts = [
        'charset'         => 'utf-8',
        'content_type'    => null,
        'max_redirects'   => 10,
        'proxy'           => null,
        'timeout'         => 10,
        'user_agent'      => 'AuraPHP/1.0 (http://auraphp.com)',
        'version'         => '1.1',
        'ssl_cafile'      => null,
        'ssl_capath'      => null,
        'ssl_local_cert'  => null,
        'ssl_passphrase'  => null,
        'ssl_verify_peer' => null,
        'method'          => self::GET,
    ];
    
    /**
     *
     * The URL to request.
     * 
     * @var string
     * 
     */
    protected $url;
    
    /**
     * 
     * HTTP request method to use.
     * 
     * @var string
     *
     */
    protected $method;
    
    /**
     * 
     * HTTP version to use.
     * 
     * @var string
     *
     */
    protected $version;
    
    /**
     * 
     * The headers to use.
     * 
     * @var array
     *
     */
    protected $headers = [];
    
    /**
     * 
     * The cookies to use.
     * 
     * @var array
     *
     */
    protected $cookies = [];
    
    /**
     * 
     * Request options to use. i.e. max_redirects, ssl_verify_peer, etc.
     * 
     * @var \ArrayObject
     * 
     * @todo list options
     *
     */
    protected $options;
    
    /**
     * 
     * The content to use for the request.
     * 
     * @var string
     *
     */
    protected $content;
    
    /**
     * 
     * Content type to use for the request.
     * 
     * @var string
     *
     */
    protected $content_type;
    
    /**
     * 
     * Charset to use for the request.
     * 
     * @var string
     *
     */
    protected $charset;
    
    /**
     * 
     * Request adapter to use.
     * 
     * @var \Aura\Http\RequestAdapter
     * 
     */
    protected $adapter;


    /**
     * 
     * @param \Aura\Http\RequestAdapter $adapter
     * 
     * @param array $opts Default options, these options survive cloning and
     * reset().
     * 
     */
    public function __construct(RequestAdapter $adapter, array $opts = [])
    {
        $this->adapter = $adapter;
        
        // Use reset to setup the default options.
        $this->reset();
        
        if ($opts) {
            $this->default_opts = array_merge($this->default_opts, $opts);
        }
    }
    
    /**
     * 
     * Reset to the constructor defaults.
     * 
     * @see reset()
     * 
     */
    public function __clone()
    {
        $this->reset();
    }
    
    /**
     * 
     * Reset to the constructor defaults.
     * 
     * @return Aura\Http\Resource
     * 
     */
    public function reset()
    {
        $this->url     = null;
        $this->content = null;
        $this->headers = [];
        $this->cookies = [];
        $this->options = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        
        $this->setDefaults();
        
        return $this;
    }
    
    /**
     * 
     * Send the HTTP request.
     *
     * @param string $save_to The file or directory to save the content to.
     * Defaults to null. 
     * 
     * @return \SplStack Ordered by last in first out.
     * 
     */
    public function send($save_to = null)
    {
        if (! $this->url) {
            throw new Exception('This request has no URL.');
        }

        // turn off encoding if we are saving the content to a file.
        if ($save_to) {
            $this->setEncoding(false);
        }

        $this->options->file = $save_to;
        
        $this->prepareContent();
        
        $this->adapter->connect($this->url);
        $this->adapter->setOptions($this->options);
        
        // force the content-type header if needed
        if ($this->content_type) { 
            if ($this->charset) {
                $this->content_type .= "; charset={$this->charset}";
            }
            $this->headers['Content-Type'] = $this->content_type;
        }
        
        // bake cookies
        if ($this->cookies) {
            $cookies = '';

            foreach ($this->cookies as $name => $value) {
                $cookies .= "{$name}={$value}; ";
            }
            $this->headers['Cookie'] = trim($cookies, '; ');
        }
        
        return $this->adapter->exec($this->method,  $this->version, 
                                    $this->headers, $this->content);
    }
    
    /**
     *
     * Save the cookies to a file. If $file is false the cookie file 
     * will be deleted. Must be a full path. 
     *
     * @param string $file 
     *
     * @return Aura\Http\Request This object.
     *
     */
    public function setCookieJar($file)
    {
        if ($file) {
            $this->options->cookiejar = $file;
        } else {
            // don't save cookies and remove the cookies file
            if (isset($this->options->cookiejar) && 
                file_exists($this->options->cookiejar)) {

                unlink($this->options->cookiejar);
            }
            unset($this->options->cookiejar);
        }

        return $this;
    }

    /**
     * 
     * Sets "Basic" or "digest" authorization credentials.
     * 
     * Note that handles may not contain colons ':'.
     * 
     * If both the handle and password are empty authorization is turned off.
     * 
     * @param string $handle The login name.
     * 
     * @param string $passwd The associated password for the handle.
     * 
     * @return Aura\Http\Request This object.
     * 
     * @throws Aura\Http\Exception\UnknownAuthType Unknown auth type.
     * 
     * @throws Aura\Http\Exception\InvalidHandle If the handle contains ':'.
     * 
     */
    public function setHttpAuth($handle, $passwd, $authtype = self::BASIC)
    {
        if (! $handle && ! $passwd) {
            unset($this->options->http_auth);
            return $this;
        }
        
        if(! in_array($authtype, [self::BASIC, self::DIGEST])) {
            throw new Exception\UnknownAuthType("Unknown auth type '$authtype'");

        } else if (strpos($handle, ':') !== false) {
            throw new Exception\InvalidHandle('The handle can not contain a colon (:)');

        }
        
        $this->options->http_auth = [$authtype, "$handle:$passwd"];
        
        return $this;
    }
    
    /**
     * 
     * Sets the URL for the request.
     * 
     * @param string $spec The URL for the request.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setUrl($spec)
    {
        if (! $this->isFullUrl($spec)) {
            throw new Exception\FullUrlExpected();
        }

        $this->url = $spec;

        return $this;
    }
        
    /**
     * 
     * Sets the HTTP method for the request (GET, POST, etc).
     * 
     * Recognized methods are OPTIONS, GET, HEAD, POST, PUT, DELETE,
     * TRACE, and CONNECT, GET, POST, PUT, DELETE, TRACE, OPTIONS, COPY,
     * LOCK, MKCOL, MOVE, PROPFIND, PROPPATCH AND UNLOCK.
     * 
     * @param string $method The method to use for the request.
     * 
     * @return Aura\Http\Request This object.
     * 
     * @throws Aura\Http\Exception\UnknownMethod 
     * 
     */
    public function setMethod($method)
    {
        $allowed = [
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
        ];
        
        if (! in_array($method, $allowed)) {
            throw new Exception\UnknownMethod("Method '{$method}' is unknown");
        }
        
        $this->method = $method;
        
        return $this;
    }
    
    /**
     * 
     * Sets the character set for the body content.
     * 
     * @param string $val The character set, e.g. "utf-8".
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setCharset($val)
    {
        $this->charset = $val;
        return $this;
    }
    
    /**
     * 
     * Sets the content-type for the body content.
     * 
     * @param string $val The content-type, e.g. "text/plain".
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setContentType($val)
    {
        $this->content_type = $val;
        return $this;
    }
    
    /**
     * 
     * Sets the body content.
     * 
     * If you pass an array, the prepare() method will automatically call
     * http_build_query() on the array and set the content-type for you.
     * 
     * @param string|array|resource $val The body content.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setContent($val)
    {
        $this->content = $val;
        return $this;
    }

    /**
     * 
     * Sets the HTTP protocol version for the request (1.0 or 1.1).
     * 
     * @param string $version The version number (1.0 or 1.1).
     * 
     * @return Aura\Http\Request This object.
     * 
     * @throws Aura\Http\Exception\UnknownVersion
     * 
     */
    public function setVersion($version)
    {
        if ($version != '1.0' && $version != '1.1') {
            throw new Exception\UnknownVersion($version);
        }
        $this->version = $version;
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
     * @return Aura\Http\Request This object.
     * 
     * @see [[php::header() | ]]
     * 
     * @throws Aura\Http\Exception Cannot use setHeader to set cookies.
     * 
     */
    public function setHeader($key, $val, $replace = true)
    {
        // normalize the header key and keep a lower-case version
        $key = $this->sanitizeLabel($key);
        $low = strtolower($key);
        
        // use special methods when available
        $special = [
            'content-type'  => 'setContentType',
            'http'          => 'setVersion',
            'referer'       => 'setReferer',
            'user-agent'    => 'setUserAgent',
        ];
        
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
        } else if ($replace || empty($this->headers[$key])) {
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
     * Sets a cookie value in $this->cookies to add to the request.
     * 
     * @param string $name The name of the cookie.
     * 
     * @param string|array $spec If a string, the value of the cookie; if an
     * array, uses the 'value' key for the cookie value.  Either way, the 
     * value will be URL-encoded at fetch() time.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setCookie($name, $spec = null)
    {
        if (is_scalar($spec)) {
            $value = (string) $spec;
        } else {
            $value = $spec['value'];
        }
        
        $name = str_replace(["\r", "\n"], '', $name);
        $this->cookies[$name] = $value;
        return $this;
    }
    
    /**
     * 
     * Sets the referer for the request.
     * 
     * @param string $spec The referer URL.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setReferer($spec)
    {
        if (! $this->isFullUrl($spec)) {
            throw new Exception\FullUrlExpected();
        }

        $this->headers['Referer'] = $spec;
        return $this;
    }
    
    /**
     * 
     * Sets the User-Agent for the request.
     * 
     * @param string $val The User-Agent value.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setUserAgent($val)
    {
        $this->headers['User-Agent'] = $val;
        return $this;
    }
    
    /**
     * 
     * Enable gzip and deflate encoding. Encoding will be disable if 
     * a path is used when sending the request.
     * 
     * @param boolean $enable
     * 
     * @return Aura\Http\Request This object.
     *
     * @see send()
     * 
     * @throws Aura\Http\Exception Zlib extension is not loaded.
     * 
     */
    public function setEncoding($encoding = true)
    {
        if ($encoding && ! function_exists('gzinflate')) {
            throw new Exception('Zlib extension is not loaded.');
        } else if (!$encoding) {
            unset($this->headers['Accept-Encoding']);
            return $this;
        }
        
        $this->headers['Accept-Encoding'] = 'gzip,deflate';
        
        return $this;
    }

    /**
     * 
     * Send all requests through this proxy server.
     * 
     * @param string $spec The URL for the proxy server.
     * 
     * @return Aura\Http\Request This object.
     * 
     * @todo username/password
     * 
     */
    public function setProxy($spec, $port = null)
    {
        if ($spec && ! $this->isFullUrl($spec)) {
            throw new Exception\FullUrlExpected();
        }

        $this->options->proxy = $spec;

        return $this;
    }
    
    /**
     * 
     * When making the request, allow no more than this many redirects.
     * 
     * @param int $max The max number of redirects to allow.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setMaxRedirects($max)
    {
        if (false === $max || null === $max) {
            $this->options->max_redirects = $this->default_opts['max_redirects'];
        } else {
            $this->options->max_redirects = (int) $max;
        }
        return $this;
    }
    
    /**
     * 
     * Sets the request timeout in seconds.
     * 
     * @param float $time The timeout in seconds.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setTimeout($time)
    {
        if (false === $time || null === $time) {
            $this->options->timeout = (float) $this->default_opts['timeout'];
        } else {
            $this->options->timeout = (float) $time;
        }
        return $this;
    }
    
    /**
     * 
     * Require verification of SSL certificate used?
     * 
     * @param bool $flag True or false.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setSslVerifyPeer($flag)
    {
        $this->options->ssl_verify_peer = (bool) $flag;
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
     * @return Aura\Http\Request This object.
     * 
     */
    public function setSslCafile($val)
    {
        $this->options->ssl_cafile = $val;
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
     * @return Aura\Http\Request This object.
     * 
     */
    public function setSslCapath($val)
    {
        $this->options->ssl_capath = $val;
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
     * @return Aura\Http\Request This object.
     * 
     */
    public function setSslLocalCert($val)
    {
        $this->options->ssl_local_cert = $val;
        return $this;
    }
    
    /**
     * 
     * Passphrase with which the $ssl_local_cert file was encoded.
     * 
     * @param string $val The passphrase.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setSslPassphrase($val)
    {
        $this->options->ssl_passphrase = $val;
        return $this;
    }

    /**
     * 
     * Prepare the content based on the HTTP request method and content type.
     * 
     * @return void
     * 
     */
    protected function prepareContent()
    {
        // what kind of request is this?
        $is_get  = ($this->method == self::GET);
        $is_post = ($this->method == self::POST);
        $is_put  = ($this->method == self::PUT);

        switch (true) {

        case (is_array($this->content) && ($is_post || $is_put)):
            // is a POST or PUT with a data array. 
            $has_files = function ($content) use (&$has_files) {
                
                foreach ($content as $key => $value) {

                    if ((is_array($value) && $has_files($value)) ||
                        '@' == $value[0]) {
                        
                        return true;
                    }
                }

                return false;
            };

            // flatten a multidimensional array - [foo => [1,2]] becomes
            // ['foo[0]' => 1, 'foo[1]' => 2]
            $flatten_content = function ($array, $return = [], $prefix = '')
                                    use (&$flatten_content)
            {
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $_prefix = $prefix ? $prefix . '[' . $key . ']' : $key;
                        $return  = $flatten_content($value, $return, $_prefix); 
                    } else {
                        $_key = $prefix ? $prefix . '[' . $key . ']' : $key;
                        $return[$_key] = $value;
                    }
                }

                return $return;
            };

            if ($has_files($this->content)) {
                $this->content      = $flatten_content($this->content);
                $this->content_type = 'multipart/form-data';
            } else {
                $this->content_type = 'application/x-www-form-urlencoded';
            }

            break;

        case (is_string($this->content)):
            // don't do anything, honour as set by the user
            break;

        case (is_array($this->content) && $is_get):
            // is a GET with a data array.
            // merge the content array with the query.
            $url          = parse_url($this->url);
            $query        = isset($url['query']) ? $url['query'] : array();
            $url['query'] = http_build_query($query + $this->content);
            $this->url    = $this->buildUrl($url);

            // now clear out the content
            $this->content      = null;
            $this->content_type = null;
            break;

        default:
            // no recognizable content
            $this->content      = null;
            $this->content_type = null;
        }
    }

    /**
     * 
     * Sanitizes header labels by removing all characters besides [a-zA-z0-9_-].
     * 
     * Underscores are converted to dashes, and word case is normalized.
     * 
     * Converts "foo \r bar_ baz-dib \n 9" to "Foobar-Baz-Dib9".
     * 
     * @param string $label The header label to sanitize.
     * 
     * @return string The sanitized header label.
     * 
     */
    protected function sanitizeLabel($label)
    {
        $label = preg_replace('/[^a-zA-Z0-9_-]/', '', $label);
        $label = ucwords(strtolower(str_replace(['-', '_'], ' ', $label)));
        $label = str_replace(' ', '-', $label);
        return $label;
    }
    
    /**
     * 
     * Setup the default options. Used by __construct, reset and __clone.
     * 
     */
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

    /**
     *
     * Build a URL from an array
     * 
     * @param array $url
     *
     * @return string
     * 
     * @see \parse_url()
     *
     */
    protected function buildUrl(array $url)
    {
        $return  = isset($url['scheme'])   ? $url['scheme'] . '://' : ''; 
        $return .= isset($url['host'])     ? $url['host']           : ''; 
        $return .= isset($url['port'])     ? ':' . $url['port']     : ''; 
        $user    = isset($url['user'])     ? $url['user']           : ''; 
        $pass    = isset($url['pass'])     ? $url['pass']           : ''; 
        $return .= ($user || $pass)        ? "$user:$pass@"         : ''; 
        $return .= isset($url['path'])     ? $url['path']           : ''; 
        $return .= isset($url['query'])    ? '?' . $url['query']    : ''; 
        $return .= isset($url['fragment']) ? '#' . $url['fragment'] : '';

        return $return;
    }

    /**
     * 
     * Check if the URL has a scheme and host.
     *
     * @param string $spec 
     *
     * @return boolean
     *
     */
    protected function isFullUrl($spec)
    {
        $url = parse_url($spec);

        return ! (empty($url['scheme']) && empty($url['host']));
    }
}