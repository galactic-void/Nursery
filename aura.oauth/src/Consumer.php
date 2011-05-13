<?php

namespace aura\oauth;

use aura\http\CurlResource as Resource;
use aura\utility\Uri as Uri;

class Consumer
{
    const DEFAULT_REQUEST_TOKEN_PATH = 'request_token';
    const DEFAULT_ACCESS_TOKEN_PATH = 'request_token';
    const DEFAULT_AUTHORIZE_PATH = 'authorize';


    const HMAC_SHA1 = 'HMAC-SHA1';
    const RSA_SHA1  = 'RSA-SHA1';
    const PLAINTEXT = 'PLAINTEXT';
    
    const HTTP = 'HTTP';
    const GET = 'GET';
    const POST = 'POST';


    protected $signature_method = self::HMAC_SHA1;
    protected $request_method   = 'GET'; //POST';
    protected $authorization_method = self::POST;//self::HTTP;
    protected $base_uri;
    protected $callback_uri = '';
    protected $consumer_secret;
    protected $consumer_key;




    protected $resource;
    protected $additional_params;
    
    protected $request_token_uri;
    protected $access_token_uri;
    protected $authorize_uri;


    public function __construct(Resource $resource, $base_uri, $consumer_secret, $consumer_key)
    {
        $base_uri = trim($base_uri);
        $base_uri = rtrim($base_uri, '/');
        
        $this->resource        = $resource;
        $this->base_uri        = $base_uri;// oauth_base_url
        $this->consumer_secret = $consumer_secret;
        $this->consumer_key    = $consumer_key;
        
        // setup default uris
        $this->request_token_uri = $base_uri . '/' . self::DEFAULT_REQUEST_TOKEN_PATH;
        $this->access_token_uri = $base_uri . '/' . self::DEFAULT_ACCESS_TOKEN_PATH;
        $this->authorize_uri = $base_uri . '/' . self::DEFAULT_AUTHORIZE_PATH;
    }
    
    public function getRequestToken()
    {
        $result             = $this->sendRequest($this->request_token_uri);
        $content            = $result->getContent();
        $oauth_token        = false !== strpos($content, 'oauth_token');
        $oauth_token_secret = false !== strpos($content, 'oauth_token_secret');
        $valid_token        = $oauth_token && $oauth_token_secret;
        
        if (200 == $result->getStatusCode() && $valid_token) {
            parse_str($content, $return);
            return $return;
        }
        \var_dump($result);
        // todo capture err info
        return false;
    }
    
    public function getAuthorization($arg)
    {
        // redirect , exit
    }
    
    public function getAuthorizationUri()
    {
        $token = $this->storage->getRequestToken();
        
        if ($token) {
            return $this->authorize_uri . "?oauth_token={$token}"; // todo use uri?
        }
        
        throw new Exception_MissingRequestToken;
    }
    
    public function getAccessToken($verifier)
    {
        
    }
    
    public function getAccess()
    {
        //combined get*
    }
    
    public function setAdditionalParams(array $params)
    {
        $this->additional_params = $params;
    }
    
    protected function sendRequest($uri)
    {
        $request = $this->resource->reset()
                        ->setUri($uri);
        
        switch ($this->authorization_method)
        {
            case self::HTTP:
                $header = $this->getAuthorizationHeader($this->getParams($uri));
                $request->setMethod($this->request_method) // todo <<
                        ->setHeader('Authorization', $header);
                
                if ($this->additional_params) {
                    $request->setContent($this->additional_params);
                }
                
                break;
            
            case self::POST:
            case self::GET:
                $params = $this->getParams($uri, $this->additional_params);
                $request->setMethod($this->authorization_method)
                        ->setContent($params);
                break;
            
            default:
                // todo excep
        }
        
        $request = $request->fetch();
        return $request[0];
    }
    
    protected function getAuthorizationHeader(array $params)
    {
        $header = 'OAuth,';
        
        foreach ($params as $key => $value) {
            $header .= "{$key}=\"{$value}\",";
        }
        
        return $header;
    }
    
    protected function getParams($uri, array $additional_params = null)
    {
        $params = array(
            'oauth_consumer_key'     => $this->consumer_key,
            'oauth_signature_method' => $this->signature_method,
            'oauth_timestamp'        => gmdate('U'),
            'oauth_nonce'            => md5(mt_rand() . microtime()),
            'oauth_version'          => '1.0',
            'oauth_callback'         => 'oob',//'http://47giraffes.com/callback.php'//$this->callback_uri,
        );
        
        if ($additional_params) {
            $params = array_merge($additional_params, $params);
        }
        
        $signature                 = $this->generateSignature($uri, $params);
        $params['oauth_signature'] = $signature;
        
        return $params;
    }
    
    protected function prepareParams(array $params)
    {
        $encode = function ($str) {
            return str_replace('%7E', '~', rawurlencode($str));
        };
        
        ksort($params, SORT_STRING);
        $return = array();
        
        foreach ($params as $key => $values) {
            if (is_array($values)) {
                sort($values, SORT_STRING);
                
                foreach ($values as $value) {
                    $return[] = $encode($key) . '=' . $encode($value);
                }
            } else {
                $return[] = $encode($key) . '=' . $encode($values);
            }
        }
        
        return implode('&', $return);
    }
    
    protected function generateSignature($uri, array $params)
    {
        $request_method = (self::HTTP == $this->authorization_method) ?
                $this->request_method : $this->authorization_method;
        
        $sign = $this->encode($request_method) . '&' .
                $this->encode($uri) . '&' . 
                $this->encode($this->prepareParams($params));
        
        switch ($this->signature_method)
        {
           case self::HMAC_SHA1:
               return $this->signHmacSha1($sign);

           case self::RSA_SHA1:
               return $this->signRsaSha1($sign);

           case self::PLAINTEXT:
               return $this->signPlainText($sign);

           default:
               throw new Exception('Unknown signature method.');
        }
    }
    
    protected function signHmacSha1($sign)
    {
        $key = $this->encode($this->consumer_secret) . '&';
        
        return base64_encode(hash_hmac('sha1', $sign, $key, true));
    }
    
    
    
    
    protected function encode($str)
    {
        return str_replace('%7E', '~', rawurlencode($str));
        return str_replace('+',  ' ', str_replace('%7E', '~', rawurlencode($str)));
    }
}

/*
 * HMACSignature etc   classes     
 */
interface OAuthStorage
{
    
}

class SessionStorage implements OAuthStorage
{
    public function getRequestToken()
    {
        return empty($_SESSION[__CLASS__]['token']) ? 
                    $_SESSION[__CLASS__]['token'] : null;
    }
    
    public function getRequestTokenSecret()
    {
        return empty($_SESSION[__CLASS__]['token_secret']) ? 
                    $_SESSION[__CLASS__]['token_secret'] : null;
    }
}