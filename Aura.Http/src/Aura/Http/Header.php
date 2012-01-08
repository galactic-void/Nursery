<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Header;

/**
 * 
 * 
 * 
 * @package Aura.Header
 * 
 */
class Header
{
    public function __construct($label, $value);
    public function __get($key);
    public function getLabel();
    public function getValue();
    public function toString();
    public function __toString();
    
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