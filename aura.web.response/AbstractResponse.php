<?php

namespace aura\web;

abstract class AbstractResponse
{
    /**
     * 
     * The response body content.
     * 
     * @var string
     * 
     */
    protected $content = null;
    
    /**
     * 
     * All headers except for cookies.
     * 
     * @var array
     * 
     */
    protected $headers = array();
    
    /**
     * 
     * All cookies.
     * 
     * @var array
     * 
     */
    protected $cookies = array();
    
    /**
     * 
     * The HTTP response status code.
     * 
     * @var int
     * 
     */
    protected $status_code = 200;
    
    /**
     * 
     * The HTTP response status text.
     * 
     * @var string
     * 
     */
    protected $status_text = null;
    
    /**
     * 
     * List of the default HTTP status text.
     * 
     * @var array
     * 
     */
    protected $default_status_text = array(
        '100' => 'Continue',
        '101' => 'Switching Protocols',

        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',

        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '307' => 'Temporary Redirect',

        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request Uri Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',

        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
    );

    /**
     * 
     * The HTTP version.
     * 
     * @var string
     * 
     */
    protected $version = '1.1';
    
    /**
     * 
     * 
     * 
     * @var aura\Mime\Mime
     * 
     */
    protected $mime;




    public function __construct(aura\Mime\Mime $mime)
    {
        $this->mime = $mime;
    }
    
    /**
     * 
     * Magic get to make the properties content, header, version, status_code,
     * status_text and cookie read-only.
     * 
     * @param string $key The property to read.
     * 
     * @return mixed The property value.
     * 
     * @throws \LogicException 
     * 
     */
    public function __get($key)
    {
        $valid = array('content', 'header', 'cookie', 'version', 
                       'status_code', 'status_text');
        
        if (in_array($key, $valid)) {
            return $this->$key;
        }
        
        throw new \LogicException("'{$key}' is protected or does not exist.");
    }
    
    /**
     *
     * Magic set to access the properties content, header, version, status_code
     * and status_text. Cookies must be set using the setCookie() method.
     * 
     * @param string $key
     * 
     * @param mixed $value 
     * 
     * @throws \LogicException 
     * 
     */
    public function __set($key, $value)
    {
        $valid = array('content', 'header', 
                       'version', 'status_code', 'status_text');
        
        if (! in_array($key, $xxx)) {
            throw new \LogicException("'{$key}' is protected or does not exist.");
        }
        
        $key = 'set' . ucfirst($key);
        $this->{$key}($value);
    }
    
    /**
     * 
     * Sets the HTTP version to '1.0' or '1.1'.
     * 
     * @param string $version The HTTP version to use for this response.
     * 
     * @return aura\web\Response This response object.
     * 
     * @throws \LogicException when the version number
     * is not '1.0' or '1.1'.
     * 
     */
    public function setVersion($version)
    {
        $version = trim($version);
        if ($version != '1.0' && $version != '1.1') {
            throw new \LogicException('Invalid HTTP version.');
        }
        
        $this->version = $version;
        
        return $this;
    }
    
    /**
     * 
     * Returns the HTTP version for this response.
     * 
     * @return string
     * 
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * 
     * Sets the HTTP response status code.
     * 
     * Automatically resets the status text to the default for this code.
     * 
     * @param int $code An HTTP status code, such as 200, 302, 404, etc.
     * 
     * @return aura\web\Response This response object.
     * 
     * @throws \LogicException when the status code is less than 100
     * or greater than 599
     * 
     */
    public function setStatusCode($code)
    {
        $code = (int) $code;
        if ($code < 100 || $code > 599) {
            throw new \LogicException('Invalid status code');
        }
        
        $this->status_code = $code;
        $this->setStatusText(null);
        
        return $this;
    }
    
    /**
     * 
     * Sets the HTTP response status text.
     * 
     * @param string $text The status text; if empty, will set the text to the
     * default for the current status code.
     * 
     * @return aura\web\Response This response object.
     * 
     */
    public function setStatusText($text)
    {
        // trim and remove newlines from custom text
        $text = trim(str_replace(array("\r", "\n"), '', $text));
        if (! $text) {
            // use default text for status code
            $text = empty($this->default_status_text[$this->status_code])
                  ? ''
                  : $this->default_status_text[$this->status_code];
        }
        
        $this->status_text = $text;
        
        return $this;
    }
    
    /**
     * 
     * Returns the current status code.
     * 
     * @return int
     * 
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }
    
    /**
     * 
     * Returns the current status text.
     * 
     * @return string
     * 
     */
    public function getStatusText()
    {
        return $this->status_text;
    }
    
    /**
     * 
     * Sets a header value in $this->headers.
     * 
     * This method will not set 'HTTP' headers for response status codes; use
     * the [[aura\web\Response::setStatusCode() | ]] and 
     * [[aura\web\Response::setStatusText() | ]] methods instead.
     * 
     * @param string $key The header label, such as "Content-Type".
     * 
     * @param string $val The value for the header.
     * 
     * @param bool $replace This header value should replace any previous
     * values of the same key.  When false, the same header key is sent
     * multiple times with the different values.
     * 
     * @return aura\web\Response This response object.
     * 
     * @throws \LogicException When trying to set a 'HTTP' header.
     * 
     * @see [[php::header() | ]]
     * 
     */
    public function setHeader($key, $val, $replace = true)
    {
        // normalize the header key
        $key = $this->mime->headerLabel($key);
        
        // disallow HTTP header
        $lower = strtolower($key);
        if ($lower == 'http') {
            $msg = 'Cannot set HTTP headers. ' .
                   'Use setStatusCode() / setStatusText() instead.';
            throw new \LogicException($msg);
        }
        
        // add the header to the list
        if ($replace || empty($this->headers[$key])) {
            // replacement, or first instance of the key
            $this->headers[$key] = $val;
        } else {
            // second or later instance of the key
            settype($this->headers[$key], 'array');
            $this->headers[$key][] = $val;
        }
        
        return $this;
    }
    
    /**
     * 
     * Returns the value of a single header unless the `$key`
     * is null then it returns all the headers and values.
     * 
     * @param string $key The header name. If null an array containing
     * all the headers will be returned.
     * 
     * @return string|array|null A string if the header has only one value, or an
     * array if the header has multiple values or `$key is null, or null 
     * if the header does not exist.
     * 
     */
    public function getHeader($key)
    {
        if (null === $key) {
            return $this->headers;
        }
        
        // normalize the header key
        $key = $this->mime->headerLabel($key);
        
        if (! empty($this->headers[$key])) {
            return $this->headers[$key];
        }
        
        return null;
    }
    
    /**
     * 
     * Sets the content of the response.
     * 
     * @param string $content The body content of the response.
     * 
     * @return aura\web\Response This response object.
     * 
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * 
     * Gets the body content of the response.
     * 
     * @return string The body content of the response.
     * 
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * 
     * Sets a cookie value in $this->cookies.
     * 
     * @param string $name The name of the cookie.
     * 
     * @param string $value The value of the cookie.
     * 
     * @param int|string $expires The Unix timestamp after which the cookie
     * expires.  If non-numeric, the method uses strtotime() on the value.
     * 
     * @param string $path The path on the server in which the cookie will be
     * available on.
     * 
     * @param string $domain The domain that the cookie is available on.
     * 
     * @param bool $secure Indicates that the cookie should only be
     * transmitted over a secure HTTPS connection.
     * 
     * @param bool $httponly When true, the cookie will be made accessible
     * only through the HTTP protocol. This means that the cookie won't be
     * accessible by scripting languages, such as JavaScript.
     * 
     * @return aura\web\Response This response object.
     * 
     * @see [[php::setcookie() | ]]
     * 
     */
    public function setCookie($name, $value = '', $expires = 0,
        $path = '', $domain = '', $secure = false, $httponly = null)
    {
        $this->cookies[$name] = array(
            'value'     => $value,
            'expires'   => $expires,
            'path'      => $path,
            'domain'    => $domain,
            'secure'    => $secure,
            'httponly'  => $httponly,
        );
        
        return $this;
    }
    
    /**
     * 
     * Returns the value and options for a single cookie unless the `$key`
     * is null then it returns the value and options for all cookies.
     * 
     * @param string $key The cookie name. If null an array containing
     * all the cookies will be returned.
     * 
     * @return array|null 
     * 
     */
    public function getCookie($key)
    {
        if (null === $key) {
            return $this->cookies;
        }
        
        if (! empty($this->cookies[$key])) {
            return $this->cookies[$key];
        }
        
        return null;
    }
}