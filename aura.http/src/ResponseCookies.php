<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace aura\http;

/**
 * 
 * Collection of cookies.
 * 
 * @package aura.http
 * 
 */
class ResponseCookies implements \IteratorAggregate
{
    protected $list = array();
    
    protected $base = array(
        'value'    => null,
        'expire'   => null,
        'path'     => null,
        'domain'   => null,
        'secure'   => false,
        'httponly' => true,
    );

    public function __clone()
    {
        $this->list = array();
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->list);
    }
    
    public function set($name, array $info = array())
    {
        $info = array_merge($this->base, $info);
        settype($info['expire'],   'int');
        settype($info['secure'],   'bool');
        settype($info['httponly'], 'bool');
        $this->list[$name] = $info;
    }
    
    public function getAll()
    {
        return $this->list;
    }
    
    public function setAll(array $cookies = array())
    {
        $this->list = array();
        foreach ($cookies as $name => $info) {
            $this->set($name, $info);
        }
    }
    
    /**
     * 
     * Parses the value of the "Set-Cookie" header value and sets it.
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
