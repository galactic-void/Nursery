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
 * @package Aura.Http
 * 
 */
interface AbstractRequest
{
    /**
     * 
     * @param \Aura\Http\RequestResponse $response
     * 
     * @param array $options Adapter specific options  
     * 
     */
    public function __construct(
         \Aura\Http\RequestResponse $response, 
         array $options = array());

    /**
     * 
     * Initialize the connection.
     * 
     * @param string $url
     * 
     * @throws Exception\ConnectionFailed
     * 
     */
    public function connect($url);


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
     * @param string $version
     * 
     * @param Headers $headers
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
    public function exec($method, $version, Headers $headers, $content);
}