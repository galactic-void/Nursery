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
class ResourceResponse extends AbstractResponse
{
    public function __clone()
    {
        $this->content     = null;
        $this->headers     = array();
        $this->cookies     = array();
        $this->status_code = 200;
        $this->status_text = null;
        $this->version     = '1.1';
    }
    
    /**
     * 
     * Set the responce content uncompressing it if necessary.
     *
     * @param string $content
     * 
     * @return aura\http\ResourseResponse 
     * 
     * @throws aura\http\Exception_UnableToUncompressContent
     * 
     */
    public function setContent($content)
    {
        $encoding = $this->getHeader('Content-Encoding');
        
        if ('gzip' == $encoding) {
            $content = $this->extractGzip($content);
        } else if ('inflate' == $encoding) {
            $content = $this->extractInflate($content);
        }
        
        if (false === $content) {
            throw new Exception_UnableToUncompressContent(func_get_arg(0));
        }
        
        return parent::setContent($content);
    }
    
    /**
     * 
     * Parses a "Set-Cookie" header value and set it using setCookie().
     * 
     * @param string $text The Set-Cookie text string value.
     * 
     * @return void
     * 
     */
    public function parseAndSetCookie($text)
    {
        $cookie = array(
            'name'      => null,
            'value'     => null,
            'expires'   => null,
            'path'      => null,
            'domain'    => null,
            'secure'    => false,
            'httponly'  => false,
        );
        
        // get the list of elements
        $list = explode(';', $text);
        
        // get the name and value
        list($cookie['name'], $cookie['value']) = explode('=', array_shift($list));
        $cookie['value'] = urldecode($cookie['value']);
        
        foreach ($list as $item) {
            $data    = explode('=', trim($item));
            $data[0] = strtolower($data[0]);
            switch ($data[0])
            {
                // string-literal values
                case 'expires':
                case 'path':
                case 'domain':
                    $cookie[$data[0]] = $data[1];
                    break;
                
                // true/false values
                case 'secure':
                case 'httponly':
                    $cookie[$data[0]] = true;
                    break;
            }
        }
        
        $this->setCookie(
            $cookie['name'],
            $cookie['value'],
            $cookie['expires'],
            $cookie['path'],
            $cookie['domain'],
            $cookie['secure'],
            $cookie['httponly']
        );
    }
    
    protected function extractGzip($content)
    {
        return @gzinflate(substr($content, 10));
    }
    
    protected function extractInflate($content)
    {
        return @gzinflate($content);
    }
}