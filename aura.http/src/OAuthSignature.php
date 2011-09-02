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
 * Base class for creating an OAuth signature.
 * 
 * @package aura.http
 * 
 */
abstract class OAuthSignature
{
    /**
     * 
     * @var Aura\Http\OAuthStorage
     *
     */
    protected $storage;

    /**
     * 
     * @var string
     *
     */
    protected $consumer_secret;

    /**
     * 
     * @var string
     *
     */
    protected $authorization_method;


    /**
     * 
     * Set the OAuth storage.
     *
     * @param Aura\Http\OAuthStorage $storage
     *
     * @return Aura\Http\OAuthSignature
     *
     */
    public function setStorage(OAuthStorage $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * 
     * Set the consumer secret.
     *
     * @param string $secret
     *
     * @return Aura\Http\OAuthSignature
     *
     */
    public function setConsumerSecret($secret)
    {
        $this->consumer_secret = $secret;
        return $this;
    }

    /**
     * 
     * Set the OAuth authorization method.
     *
     * @param string $method
     *
     * @return Aura\Http\OAuthSignature
     *
     */
    public function setAuthorizationMethod($method)
    {
        $this->authorization_method = $method;
        return $this;
    }

    /**
     * 
     * Generate the OAuth signature.
     *
     * @param string $uri
     *
     * @param string $method The HTTP method to use if the authorization
     * method is HTTP
     *
     * @param array $params
     *
     * @return string
     *
     */
    public function generateSignature($uri, $method, array $params)
    {
        $request_method = (OAuth::HTTP == $this->authorization_method) ?
                           $method : $this->authorization_method;
        
        // scheme and host must be lower case 
        $uri           = parse_url($uri);
        $uri['scheme'] = strtolower($uri['scheme']);
        $uri['host']   = strtolower($uri['host']);

        // and only include the port number if not 80 for http or 443 for https.
        if (! empty($uri['port']) && 
            (('http'  == $uri['scheme'] && 80  == $uri['port']) ||
             ('https' == $uri['scheme'] && 443 == $uri['port'])
            )) {
            
            unset($uri['port']);
        }

        $uri = $this->buildUri($uri);

        // build the signature base string
        $sbs = rawurlencode($request_method) . '&' .
               rawurlencode($uri) . '&' .
               rawurlencode($this->prepareParams($params));
        
        return $this->sign($sbs);
    }

    /**
     * 
     * Sign the signature base string.
     *
     * @param string $sbs Signature base string.
     *
     * @return string
     *
     */
    abstract protected function sign($sbs);

    /**
     *
     * Normalize the request parameters.
     *
     * @param array $params
     *
     * @return string
     *
     */
    protected function prepareParams(array $params)
    {
        // sort the params by key
        ksort($params, SORT_STRING);
        $return = array();
        
        foreach ($params as $key => $values) {
            if (is_array($values)) {
                // when a key has more than one value sort the values
                sort($values, SORT_STRING);
                
                // keys and values get encoded as per RFC 1738.
                foreach ($values as $value) {
                    $return[] = rawurlencode($key) . '=' . rawurlencode($value);
                }
            } else {
                $return[] = rawurlencode($key) . '=' . rawurlencode($values);
            }
        }
        
        return implode('&', $return);
    }

    /**
     * 
     * Recreate the uri from a parse_url array.
     *
     * @param array $uri
     *
     * @return string
     *
     */
    protected function buildUri(array $uri)
    {
        $pass = ((isset($uri['pass'])) ? ':' . $uri['pass'] : '');

        return 
            $uri['scheme'] . '://' .
            ((isset($uri['user']))     ? $uri['user'] . $pass . '@' : '') .
            $uri['host'] .
            ((isset($uri['port']))     ? ':' . $uri['port'] : '') .
            ((isset($uri['path']))     ? $uri['path'] : '') .
            ((isset($uri['query']))    ? '?' . $uri['query'] : '') .
            ((isset($uri['fragment'])) ? '#' . $uri['fragment'] : '');
    }
}