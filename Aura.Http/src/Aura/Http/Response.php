<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

/**
 * 
 * The Aura Response class
 * 
 * @package Aura.Http
 * 
 */
class Response extends Request\Response
{   
    /** 
     * 
     * Is the response currently acting in CGI mode.
     * 
     * @var boolean
     * 
     */
    protected $is_cgi;
    
    /**
     * 
     * Constructor.
     * 
     * @param Aura\Http\Headers $headers A Headers object.
     * 
     * @param Aura\Http\Cookies $cookies A Cookies object.
     * 
     */
    public function __construct(Headers $headers, Cookies $cookies)
    {
        $this->headers = $headers;
        $this->cookies = $cookies;
        $this->setStatusCode(200);
        $is_cgi = (strpos(php_sapi_name(), 'cgi') !== false);
        $this->setCgi($is_cgi);
    }
    
    /**
     * 
     * Optionally force the response to act as if it is in CGI mode. (This
     * changes how the status header is sent.)
     * 
     * @param boolean $is_cgi True to force into CGI mode, false to not do so.
     * 
     * @return void
     * 
     */
    public function setCgi($is_cgi)
    {
        $this->is_cgi = (bool) $is_cgi;
    }
    
    /**
     * 
     * Is the response currently acting in CGI mode?
     * 
     * @return boolean
     * 
     */
    public function isCgi()
    {
        return (bool) $this->is_cgi;
    }
    
    /**
     * 
     * Sends the full HTTP response.
     * 
     * @return void
     * 
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
    }
    
    /**
     * 
     * Sends the HTTP status code, status test, headers, and cookies.
     * 
     * @return void
     * 
     */
    public function sendHeaders()
    {
        if (headers_sent($file, $line)) {
            throw new Http\Exception\HeadersSent($file, $line);
        }
        
        // determine status header type
        // cf. <http://www.php.net/manual/en/function.header.php>
        if ($this->isCgi()) {
            $status = "Status: {$this->status_code}";
        } else {
            $status = "HTTP/{$this->version} {$this->status_code}";
        }
        
        // add status text
        if ($this->status_text) {
            $status .= " {$this->status_text}";
        }
        
        // send the status header
        header($status, true, $this->status_code);
        
        // send the non-cookie headers
        $this->headers->send();
        
        // send the cookie headers
        $this->cookies->send();
    }
    
    /**
     * 
     * Sends the HTTP content; if the content is a resource, it streams out
     * the resource 8192 bytes at a time.
     * 
     * @return void
     * 
     */
    public function sendContent()
    {
        $content = $this->getContent();
        if (is_resource($content)) {
            while (! feof($content)) {
                echo fread($content, 8192);
            }
            fclose($content);
        } else {
            echo $content;
        }
    }
    
    /** 
     * 
     * Sets the cookies for the response.
     * 
     * @param Aura\Http\Cookies $cookies The cookies object.
     * 
     * @return void
     * 
     */
    public function setCookies(Cookies $cookies)
    {
        $this->cookies = $cookies;
    }
    
    /** 
     * 
     * Returns the $cookies object.
     * 
     * @return Aura\Http\Cookies
     * 
     */
    public function getCookies()
    {
        return $this->cookies;
    }
    
    /**
     * 
     * Sets the headers for the response (not including cookies).
     * 
     * @param Aura\Http\Headers $headers A Headers object.
     * 
     * @return void
     * 
     */
    public function setHeaders(Headers $headers)
    {
        $this->headers = $headers;
    }
    
    /**
     * 
     * Returns the headers for the response (not including cookies).
     * 
     * @return Aura\Http\Headers
     * 
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * 
     * Sets the content of the response.
     * 
     * @param mixed $content The body content of the response. Note that this
     * may be a resource, in which case it will be streamed out when sending.
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
     * @return mixed The body content of the response.
     * 
     */
    public function getContent()
    {
        return $this->content;
    }
}