<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Storage;


/**
 * 
 * Session storage for OAuth tokens and secrets.
 * 
 * @package aura.http
 * 
 */
class Session implements \Aura\Http\OAuthStorage
{
    const DEFAULT_NAMESPACE = 'default';

    protected $namespace = self::DEFAULT_NAMESPACE;

    /**
     * 
     * Is the stored token a request token.
     *
     * @return bool
     *
     */
    public function isRequestToken()
    {
        $token = !empty($_SESSION[__CLASS__][$this->namespace]['token_type']) ? 
                        $_SESSION[__CLASS__][$this->namespace]['token_type'] : null;

        return self::REQUEST == $token;
    }

    /**
     * 
     * Is the stored token an access token.
     *
     * @return bool
     *
     */
    public function isAccessToken()
    {
        $token = !empty($_SESSION[__CLASS__][$this->namespace]['token_type']) ? 
                        $_SESSION[__CLASS__][$this->namespace]['token_type'] : null;

        return self::ACCESS == $token;
    }

    /**
     * 
     * Set the namespace. When using multipliable providers a namespace is required
     * to differentiate the providers tokens and secrets.
     *
     * @param string $namespace The namespace i.e. twitter, facebook, google, etc.
     *
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * 
     * Set the token type: either Aura\Http\OAuthStorage::REQUEST or 
     * Aura\Http\OAuthStorage::ACCESS. 
     *
     * @return string $type
     *
     */
    public function setTokenType($type)
    {
        $_SESSION[__CLASS__][$this->namespace]['token_type'] = $type;
    }

    /**
     * 
     * Set the token. 
     *
     * @return string $token
     *
     */
    public function setToken($token)
    {
        $_SESSION[__CLASS__][$this->namespace]['token'] = $token;
    }
    
    /**
     * 
     * Set the token secret. 
     *
     * @return string $token
     *
     */
    public function setTokenSecret($token)
    {
        $_SESSION[__CLASS__][$this->namespace]['token_secret'] = $token;
    }

    /**
     * 
     * Delete the token and secret in this namespace.
     *
     */
    public function delete()
    {
        unset($_SESSION[__CLASS__][$this->namespace]);
    }

    /**
     * 
     * Get the token. If the token does not exist null is returned
     *
     * @return string|null
     *
     */
    public function getToken()
    {
        return !empty($_SESSION[__CLASS__][$this->namespace]['token']) ? 
                      $_SESSION[__CLASS__][$this->namespace]['token'] : null;
    }
    
    /**
     * 
     * Get the token secret. If the secret does not exist null is returned
     *
     * @return string|null
     *
     */
    public function getTokenSecret()
    {
        return !empty($_SESSION[__CLASS__][$this->namespace]['token_secret']) ? 
                      $_SESSION[__CLASS__][$this->namespace]['token_secret'] : null;
    }
}
