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
 * OAuth connection manager for connecting to multipliable OAuth providers.
 * 
 * @package aura.http
 * 
 */
 class OAuthConnectionManager
 {
    protected $list = array();

    /**
     * 
     * @param array $connections array of OAuth connections with the `key` being
     * the name. Optional.
     *
     */
    public function __construct(array $connections = array())
    {
        foreach ($connections as $name => $oauth) {
            $this->set($name, $oauth);
        }
    }

    /**
     * 
     * Fetch a configured OAuth object.
     *
     * @param string $name 
     *
     * @return Aura\Http\Oauth
     *
     * @throws Aura\Http\Exception\UnknownConnection
     *
     */
    public function get($name)
    {
        if (empty($this->list[$name])) {
            throw new Exception\UnknownConnection("OAuth connection `{$name}` was not found.");
        }

        return $this->list[$name];
    }

    /**
     * 
     * Set a configured OAuth connection.
     *
     * @param string $name
     *
     * @param Aura\Http\OAuth
     *
     */
    public function set($name, OAuth $oauth)
    {
        $this->list[$name] = $oauth;
    }

    /**
     * 
     * List all stored OAuth connections.
     *
     * @return array
     *
     */
    public function listAll()
    {
        return $this->list;
    }
 }
 