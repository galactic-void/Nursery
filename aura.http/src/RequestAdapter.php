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
 * HTTP Request library.
 * 
 * @package aura.http
 * 
 */
interface RequestAdapter
{
    /**
     * 
     * Initialize the connection.
     * 
     * @param string $url
     * 
     * @param string $version
     * 
     * @throws Exception\ConnectionFailed
     * 
     */
    public function connect($url, $version);


    /**
     * 
     * The request options.
     * 
     * @param \ArrayObject $options
     * 
     */
    public function setOptions(\ArrayObject $options);


    /**
     * 
     * Execute the request.
     * 
     * @param string $method
     * 
     * @param array $headers
     * 
     * @param string $content
     * 
     * @return \SplStack
     * 
     * @throws Exception\ConnectionFailed
     * 
     * @throws Exception\EmptyResponse
     * 
     */
    public function exec($method, array $headers, $content);
}