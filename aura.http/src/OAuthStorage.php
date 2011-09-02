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
 * Interface for the storage for OAuth tokens and secrets.
 * 
 * @package aura.http
 * 
 */
 interface OAuthStorage
{
    /**
     * Token types
     */
    const REQUEST = 1;
    const ACCESS  = 2;


    /**
     * 
     * Is the stored token a request token.
     *
     * @return bool
     *
     */
    public function isRequestToken();

    /**
     * 
     * Is the stored token an access token.
     *
     * @return bool
     *
     */
    public function isAccessToken();

    /**
     * 
     * Set the namespace. When using multipliable providers a namespace is required
     * to differentiate the providers tokens and secrets.
     *
     * @param string $namespace The namespace i.e. twitter, facebook, google, etc.
     *
     */
    public function setNamespace($namespace);

    /**
     * 
     * Set the token type: either Aura\Http\OAuthStorage::REQUEST or 
     * Aura\Http\OAuthStorage::ACCESS. 
     *
     * @return string $type
     *
     */
    public function setTokenType($type);

    /**
     * 
     * Set the token. 
     *
     * @return string $token
     *
     */
    public function setToken($token);
    
    /**
     * 
     * Set the token secret. 
     *
     * @return string $token
     *
     */
    public function setTokenSecret($token);

    /**
     * 
     * Delete the token and secret in this namespace.
     *
     */
    public function delete();

    /**
     * 
     * Get the token. If the token does not exist null is returned
     *
     * @return string|null
     *
     */
    public function getToken();
    
    /**
     * 
     * Get the token secret. If the secret does not exist null is returned
     *
     * @return string|null
     *
     */
    public function getTokenSecret();
}
