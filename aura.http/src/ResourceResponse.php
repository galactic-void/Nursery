<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace aura\http;


/**
 * 
 * 
 * 
 * @package aura.http
 * 
 */
class ResourceResponse
{

    protected $cookies = array();
    
    protected $content;
    
    protected $headers = array();
    
    protected $status_code;
    
    protected $status_text;
    
    /**
     * 
     * List of the HTTP status text default values.
     * 
     * @var array
     * 
     */
    protected $status_text_default = array(
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
    
    protected $version = '1.1';
    
    public function __construct(
        ResponseHeaders $headers,
        ResponseCookies $cookies
    )
    {
        $this->headers = $headers;
        $this->cookies = $cookies;
    }
    
    public function __clone()
    {
        $this->content     = null;
        $this->headers     = clone $this->headers;
        $this->cookies     = clone $this->cookies;
        $this->status_code = 200;
        $this->status_text = null;
        $this->version     = '1.1';
    }
    
    public function __get($key)
    {
        if ($key == 'headers') {
            return $this->headers;
        } else if ($key == 'cookies') {
            return $this->cookies;
        }
        
        throw new Exception("No such property '$key'");
    }
    
    public function setCookies(ResponseCookies $cookies)
    {
        $this->cookies = $cookies;
    }
    
    public function getCookies()
    {
        return $this->cookies;
    }
    
    /**
     * 
     * Set the response content.
     *
     * @param string $content
     * 
     * @return void
     * 
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    /**
     * 
     * Gets the content of the response.
     * 
     * @throws aura\http\Exception_UnableToDecompressContent
     * 
     * @return string The body content of the response.
     * 
     */
    public function getContent()
    {
        $encoding = $this->header->get('Content-Encoding');
        
        if ('gzip' == $encoding) {
            $content = @gzinflate(substr($this->content, 10));
        } else if ('inflate' == $encoding) {
            $content = @gzinflate($this->content);
        } else {
            return $this->content;
        }
        
        if (false === $content) {
            throw new Exception_UnableToDecompressContent($this->content);
        }
        
        return $content;
    }
    
    /**
     * 
     * Sets the headers for the response (excluding cookies).
     * 
     * @param array $headers An array of headers.
     * 
     * @return void
     * 
     */
    public function setHeaders(ResponseHeaders $headers)
    {
        $this->headers = $headers;
    }
    
    /**
     * 
     * Returns the headers for the response (not including cookies).
     * 
     * @return array An array of headers.
     * 
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * 
     * Sets the HTTP status code to for the response.
     * 
     * Automatically resets the status text to null.
     * 
     * @param int $code An HTTP status code, such as 200, 302, 404, etc.
     * 
     */
    public function setStatusCode($code)
    {
        $code = (int) $code;
        if ($code < 100 || $code > 599) {
            throw new Exception("Status code '$code' not recognized.");
        }
        
        $this->status_code = $code;
        
        if (isset($this->status_text_default[$code])) {
            $this->setStatusText($this->status_text_default[$code]);
        } else {
            $this->setStatusText(null);
        }
    }
    
    /**
     * 
     * Returns the HTTP status code for the response.
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
     * Sets the HTTP status text for the response.
     * 
     * @param string $text The status text.
     * 
     * @return void
     * 
     */
    public function setStatusText($text)
    {
        $text = trim(str_replace(array("\r", "\n"), '', $text));
        $this->status_text = $text;
    }
    
    /**
     * 
     * Returns the HTTP status text for the response.
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
     * Sets the HTTP version for the response to '1.0' or '1.1'.
     * 
     * @param string $version The HTTP version to use for this response.
     * 
     * @return void
     * 
     */
    public function setVersion($version)
    {
        $version = trim($version);
        if ($version != '1.0' && $version != '1.1') {
            throw new Exception("HTTP version '$version' not recognized.");
        } else {
            $this->version = $version;
        }
    }
    
    /**
     * 
     * Returns the HTTP version for the response.
     * 
     * @return string
     * 
     */
    public function getVersion()
    {
        return $this->version;
    }
}