<?php

namespace Aura\Http\Storage;

class MockStorage implements \Aura\Http\OAuthStorage
{
    const DEFAULT_NAMESPACE = 'default';

    protected $namespace = self::DEFAULT_NAMESPACE;
    protected $store     = array();

    /**
     * 
     * Is the stored token a request token.
     *
     * @return bool
     *
     */
    public function isRequestToken()
    {
        $token = !empty($this->store[__CLASS__][$this->namespace]['token_type']) ? 
                        $this->store[__CLASS__][$this->namespace]['token_type'] : null;

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
        $token = !empty($this->store[__CLASS__][$this->namespace]['token_type']) ? 
                        $this->store[__CLASS__][$this->namespace]['token_type'] : null;

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
        $this->store[__CLASS__][$this->namespace]['token_type'] = $type;
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
        $this->store[__CLASS__][$this->namespace]['token'] = $token;
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
        $this->store[__CLASS__][$this->namespace]['token_secret'] = $token;
    }

    /**
     * 
     * Delete the token and secret in this namespace.
     *
     */
    public function delete()
    {
        unset($this->store[__CLASS__][$this->namespace]);
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
        return !empty($this->store[__CLASS__][$this->namespace]['token']) ? 
                      $this->store[__CLASS__][$this->namespace]['token'] : null;
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
        return !empty($this->store[__CLASS__][$this->namespace]['token_secret']) ? 
                      $this->store[__CLASS__][$this->namespace]['token_secret'] : null;
    }
}
