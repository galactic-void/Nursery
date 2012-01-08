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
 * Collection of non-cookie HTTP headers.
 * 
 * @package Aura.Http
 * 
 */
class ResponseHeaders implements \IteratorAggregate, \Countable
{
    /**
     * 
     * The list of all headers.
     * 
     * @var array
     * 
     */
    protected $list = array();
    
    /**
     * 
     * Reset the list of headers.
     * 
     */
    public function __clone()
    {
        $this->list = array();
    }
    
    /**
     * 
     * Get a header.
     * 
     * @param string $key 
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        return $this->list[$key];
    }
    
    /**
     * 
     * Does a header exist.
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
     * Count the number of headers.
     * 
     * @return integer
     * 
     */
    public function count()
    {
        return count($this->list, COUNT_RECURSIVE);
    }
    
    /**
     * 
     * Returns all the headers.
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
     * Returns all the headers as an iterator.
     * 
     * @return \ArrayIterator
     * 
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->list);
    }
    
    /**
     * 
     * Adds a header value to an existing header label; if there is more
     * than one, it will append the new value.
     * 
     * @param string $label The header label.
     * 
     * @param string $value The header value.
     * 
     * @return void
     * 
     */
    public function add($label, $value)
    {
        if ($name instanceof Header) {
            $header = $name;
        } else {
            $header = $this->factory->newInstance($label, $value);
        }

        $this->list[$header->getName()][] = $header;
    }
    
    /**
     * 
     * Sets a header value, overwriting previous values.
     * 
     * @param string $label The header label.
     * 
     * @param string $value The header value.
     * 
     * @return void
     * 
     */
    public function set($label, $value)
    {
        if ($name instanceof Header) {
            $header = $name;
        } else {
            $header = $this->factory->newInstance($label, $value);
        }

        $this->list[$header->getName()] = array($header);
    }
    
    /**
     * 
     * Sets all the headers at once; replaces all previously existing headers.
     * 
     * @param array $headers An array of headers where the key is the header
     * label, and the value is the header value (multiple values are allowed).
     * 
     * @return void
     * 
     */
    public function setAll(array $headers = array())
    {
        foreach ($headers as $label => $values) {
            foreach ((array) $values as $value) {
                $this->add($label, $value);
            }
        }
    }
    
    /**
     * 
     * Sends all the headers using `header()`.
     * 
     * @return void
     * 
     */
    public function send()
    {
        foreach ($this->list as $values) {
            foreach ($values as $header) {
                header("$header->getLabel(): $header->getValue()");
            }
        }
    }
}
