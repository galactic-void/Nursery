<?php

class SignedValue
{
    const SEPARATOR = '|';
    
    /**  @var string  */
    protected $hash_algo;
    
    /**  @var string  */
    protected $secret_key;
    
    /**
     *
     * NOTE: Each project should have a unique and random $secret_key.
     * 
     * @param string $secret_key
     * 
     * @param string $hash_algo 
     * 
     */
    public function __construct($secret_key, $hash_algo = 'sha1')
    {
        $this->secret_key = $secret_key;
        $this->hash_algo  = $hash_algo;
    }
    
    /**
     * 
     * Magic get to provide access to the hash_algo variable.
     * 
     * @throws \LogicException
     * 
     * @param string $key The property to retrieve: hash_algo & timeout.
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        if ($key == 'hash_algo') {
            return $this->$key;
        }
        
        throw new \LogicException("'{$key}' is protected or does not exist.");
    }
    
    /**
     * 
     * Magic set to provide access to the secret_key and hash_algo 
     * variables.
     * 
     * @throws \UnexpectedValueException
     * 
     * @param string $key The property to set: secret_key, hash_algo & timeout.
     * 
     * @return void
     * 
     */
    public function __set($key, $value)
    {
        if ($key == 'secret_key' || $key == 'hash_algo') {
            $this->$key = $value;
            return;
        }
        
        throw new \LogicException("'{$key}' is protected or does not exist.");
    }
    
    /**
     * 
     * Extract and validate a value from a signed value.
     * 
     * @throws Exception_InvalidSignature
     * 
     * @throws Exception_ExpiredSignature
     * 
     * @param string $value The signed value.
     * 
     * @return string The validate value.
     * 
     */
    public function getSignedValue($value)
    {
        if (3 != substr_count($string, static::SEPARATOR)) {
            throw new Exception_InvalidSignature();
        }
        
        list($value, $time, $expires, $hash) = explode(static::SEPARATOR, $value);
        
        $sign = $value . static::SEPARATOR . 
                $time  . static::SEPARATOR . 
                $expires;
        
        if ($hash != $this->signature($sign)) {
            throw new Exception_InvalidSignature();
        } else if (! empty($expires) && $time + $expires <= time()) {
            throw new Exception_ExpiredSignature();
        }
        
        return base64_decode($value);
    }
    
    /**
     * 
     * Sign a value with the option to expire after X seconds.
     * 
     * @param string $value
     * 
     * @param int    $expires Expires in X seconds. Default: null; Never expires.
     * 
     * @return string Signed value. 
     * 
     */
    public function sign($value, $expires = null)
    {
        // value|time|expire|signature
        $value = base64_encode($value);
        $time  = time();
        $value = $value . static::SEPARATOR . 
                 $time  . static::SEPARATOR . 
                 $expires;
        
        return $value . static::SEPARATOR . $this->signature($value);
    }
    
    protected function signature($value)
    {
        return hash_hmac($this->hash_algo, $value, $this->secret_key);
    }
}