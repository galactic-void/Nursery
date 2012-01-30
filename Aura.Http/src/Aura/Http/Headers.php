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
class Headers extends Request\Headers
{
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
                header($header->toString());
            }
        }
    }
}
