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
 * Collection of cookies.
 * 
 * @package aura.http
 * 
 */
class ResponseCookies implements \IteratorAggregate
{
    /**
     * 
     * The list of all cookies.
     * 
     * @var array
     * 
     */
    protected $list = array();

    /**
     * 
     * Base values for a single cookie.
     * 
     * @todo Extract to a Cookie struct, and probably a CookieFactory.
     * 
     * @var array
     * 
     */
    protected $base = array(
        'value'    => null,
        'expire'   => null,
        'path'     => null,
        'domain'   => null,
        'secure'   => false,
        'httponly' => true,
    );

    /**
     * 
     * Reset the cookie list.
     * 
     */
    public function __clone()
    {
        $this->list = array();
    }
    
    /**
     * 
     * Get a cookie.
     * 
     * @param string $key 
     * 
     * @return array
     * 
     */
    public function __get($key)
    {
        return $this->list[$key];
    }
    
    /**
     * 
     * Does a cookie exist.
     * 
     * @param string $key 
     * 
     * @return bool
     * 
     */
    public function __isset($key)
    {
        return isset($this->list[$key]);
    }
    
    /** 
     * 
     * Gets all cookies as an iterator.
     * 
     * @return array
     * 
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->list);
    }
    
    /**
     * 
     * Sets a single cookie by name.
     * 
     * @param string $name The cookie name.
     * 
     * @param array $info The cookie info.
     * 
     */
    public function set($name, array $info = array())
    {
        $info = array_merge($this->base, $info);
        settype($info['expire'],   'int');
        settype($info['secure'],   'bool');
        settype($info['httponly'], 'bool');
        $this->list[$name] = $info;
    }
    
    /** 
     * 
     * Gets all cookies.
     * 
     * @return array
     * 
     */
    public function getAll()
    {
        return $this->list;
    }
    
    /**
     * 
     * Sets all cookies at once.
     * 
     * @param array $cookies The array of all cookies where the key is the
     * name and the value is the array of cookie info.
     * 
     * @return void
     * 
     */
    public function setAll(array $cookies = array())
    {
        $this->list = array();
        foreach ($cookies as $name => $info) {
            $this->set($name, $info);
        }
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
        $cookie['value']                        = urldecode($cookie['value']);
        
        foreach ($list as $item) {
            $data    = explode('=', trim($item));
            $data[0] = strtolower($data[0]);
            
            switch ($data[0]) {
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
        
        $this->set(
            $cookie['name'],
            array(
                'value'    => $cookie['value'],
                'expire'   => $cookie['expires'],
                'path'     => $cookie['path'],
                'domain'   => $cookie['domain'],
                'secure'   => $cookie['secure'],
                'httponly' => $cookie['httponly'],
            )
        );
    }
}
