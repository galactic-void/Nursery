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
 * @package aura.http
 * 
 */
class ResponseHeaders implements \IteratorAggregate
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
        $label = $this->sanitizeLabel($label);
        $this->list[$label][] = $value;
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
        $label = $this->sanitizeLabel($label);
        $this->list[$label] = array($value);
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
        $label = ucwords(strtolower(str_replace(array('-', '_'), ' ', $label)));
        $label = str_replace(' ', '-', $label);
        return $label;
    }
}
