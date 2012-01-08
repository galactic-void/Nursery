<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

/**
 * 
 * 
 * 
 * @package Aura.Http
 * 
 */
class Cookie
{
    protected $name;
    protected $value;
    protected $expire;
    protected $path;
    protected $domain;
    protected $secure;
    protected $httponly;

    public function __construct(
        $name, 
        $value, 
        $expire, 
        $path, 
        $domain, 
        $secure, 
        $httponly)
    {
        $this->name     = $name;
        $this->value    = $value;
        $this->expire   = $expire;
        $this->path     = $path;
        $this->domain   = $domain;
        $this->secure   = $secure;
        $this->httponly = $httponly;
    }

    public function __get($key)
    {
        return $this->$key;
    }

    /**
     * 
     * Parses the value of the "Set-Cookie" header and sets it.
     * 
     * @param string $text The Set-Cookie text string value.
     * 
     * @return void
     * 
     */
    public function setFromString($text)
    {
        // get the list of elements
        $list = explode(';', $text);
        
        // get the name and value
        list($this->name, $this->value) = explode('=', array_shift($list));
        $this->value                    = urldecode($this->value);
        
        foreach ($list as $item) {
            $data    = explode('=', trim($item));
            $data[0] = strtolower($data[0]);
            
            switch ($data[0]) {
            // string-literal values
            case 'expires':
                $this->expire = $data[1];
                break;

            case 'path':
            case 'domain':
                $this->$data[0] = $data[1];
                break;
            
            // true/false values
            case 'secure':
            case 'httponly':
                $this->$data[0] = true;
                break;
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }
    
    public function getExpire()
    {
        return $this->expire;
    }
    
    public function getPath()
    {
        return $this->parse_url(url);
    }
    
    public function getDomain()
    {
        return $this->domain;
    }
    
    public function getSecure()
    {
        return $this->secure;
    }
    
    public function getHttpOnly()
    {
        return $this->httponly;
    }
    
    public function toString()
    {
        // value[; expires=date][; domain=domain][; path=path][; secure]
    }

    public function __toString()
    {
        return $this->toString();
    }
}