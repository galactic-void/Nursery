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
 * OAuth consumer. 
 * 
 * @package aura.http
 * 
 */
class OAuth
{
    /**
     * Default path fragments.
     */
    const REQUEST_TOKEN_PATH = 'request_token';
    const ACCESS_TOKEN_PATH  = 'access_token';
    const AUTHORIZE_PATH     = 'authorize';

    /**
     * Signature methods
     */
    const HMAC_SHA1 = 'HMAC-SHA1';
    const RSA_SHA1  = 'RSA-SHA1';
    const PLAINTEXT = 'PLAINTEXT';
    
    /**
     * Valid OAuth HTTP request methods.
     */
    const HTTP = 'HTTP';
    const GET  = 'GET';
    const POST = 'POST';

    /**
     * 
     * @var \Aura\Http\Request
     *
     */
    protected $request;

    /**
     * 
     * @var \Aura\Http\Response
     *
     */
    protected $response;

    /**
     * 
     * @var \Aura\Http\OAuthStorage
     *
     */
    protected $storage;

    /**
     * 
     * @var \Aura\Http\OAuthSignature
     *
     */
    protected $signature;

    /**
     * 
     * @var string
     *
     */
    protected $base_url;

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
    protected $consumer_key;

    /**
     * 
     * @var string
     *
     */
    protected $callback;

    /**
     * 
     * @var string
     *
     */
    protected $signature_method = self::HMAC_SHA1;

    /**
     * 
     * @var string
     *
     */
    protected $request_method = self::GET;

    /**
     * 
     * @var string
     *
     */
    protected $authorization_method;

    /**
     * 
     * @var array
     *
     */
    protected $parameters;

    /**
     * 
     * @var string
     *
     */
    protected $request_token_uri;

    /**
     * 
     * @var string
     *
     */
    protected $access_token_uri;

    /**
     * 
     * @var string
     *
     */
    protected $authorize_uri;


    /**
     * 
     * @param \Aura\Http\Request  $request
     *
     * @param \Aura\Http\Response $response
     *
     * @param \Aura\Http\OAuthStorage $storage
     *
     * @param \Aura\Http\OAuthSignature $signature
     *
     * @param string $base_uri
     *
     * @param string $authorization_method
     *
     * @param string $consumer_key
     *
     * @param string $consumer_secret
     * 
     */
    public function __construct(
        Request $request, 
        Response $response, 
        OAuthStorage $storage,
        OAuthSignature $signature,
        $base_uri, 
        $authorization_method, 
        $consumer_key, 
        $consumer_secret)
    {
        $base_uri = trim($base_uri);
        $base_uri = rtrim($base_uri, '/');
        
        $this->request              = $request;
        $this->response             = $response;
        $this->storage              = $storage;
        $this->storage->setNamespace(md5($base_uri));
        $this->signature            = $signature->setStorage($storage)
                                                ->setConsumerSecret($consumer_secret)
                                                ->setAuthorizationMethod($authorization_method);
        $this->base_uri             = $base_uri;
        $this->consumer_secret      = $consumer_secret;
        $this->consumer_key         = $consumer_key;
        $this->authorization_method = $authorization_method;
        
        // setup the default uris
        $this->request_token_uri = $base_uri . '/' . self::REQUEST_TOKEN_PATH;
        $this->access_token_uri  = $base_uri . '/' . self::ACCESS_TOKEN_PATH;
        $this->authorize_uri     = $base_uri . '/' . self::AUTHORIZE_PATH;
    }

    /**
     * 
     * Do we have a request token.
     *
     * @return bool
     *
     */
    public function hasRequestToken()
    {
        return (bool) $this->storage->isRequestToken() && 
                      null !== $this->storage->getToken();
    }

    /**
     * 
     * Do we have an access token.
     *
     * @return bool
     *
     */
    public function hasAccessToken()
    {
        return (bool) $this->storage->isAccessToken() && 
                      null !== $this->storage->getToken();
    }

    /**
     * 
     * If the authorization method is set to OAuth::HTTP use this HTTP
     * request method when making token requests. Defaults to GET.
     *
     * @param string $method HTTP method
     *
     * @return \Aura\Http\OAuth
     *
     */
    public function setRequestMethod($method)
    {
        $this->request_method = strtoupper($method);
        return $this;
    }

    /**
     * 
     * Change the OAuth authorization method.
     *
     * @param string $method 
     *
     * @return \Aura\Http\OAuth
     *
     */
    public function setAuthorizationMethod($method)
    {
        $this->authorization_method = $method;
        return $this;
    }

    /**
     * 
     * Set the callback url.
     *
     * @param string $url
     *
     * @return \Aura\Http\OAuth
     *
     */
    public function setCallback($url)
    {
        $this->callback = $url;
        return $this;
    }

    /**
     * 
     * The request token uri.
     *
     * @return string
     *
     */
    public function getRequestTokenUri()
    {
        // clear any tokens and secrets.
        $this->storage->delete();

        return $this->request_token_uri;
    }

    /**
     * 
     * Set the full request token uri.
     *
     * @param string $uri
     *
     * @return \Aura\Http\OAuth
     *
     */
    public function setRequestTokenUri($uri)
    {
        $this->request_token_uri = $uri;
        return $this;
    }

    /**
     * 
     * Get the current OAuth authorization method.
     *
     * @return string
     *
     */
    public function getAuthorizationMethod()
    {
        return $this->authorization_method;
    }

    /**
     * 
     * Create the user authorization uri.
     *
     * @param array $optional_params
     *
     * @return string
     *
     * @throws Aura\Http\Exception\MissingRequestToken If the request token is 
     * not found.
     *
     */
    public function getUserAuthorizationUrl(array $optional_params = array())
    {
        $token = $this->storage->getToken();
        $optional_params = $optional_params ? '&' . http_build_query($optional_params) : '';

        if ($token) {
            return $this->authorize_uri . 
                   "?oauth_token={$token}" .
                   $optional_params;
        }
        
        throw new Exception\MissingRequestToken('Can not create the authorization uri, no request token found.');
    }

    /**
     * 
     * Set the full authorize uri.
     *
     * @param string $uri
     *
     * @return \Aura\Http\OAuth
     *
     */
    public function setAuthorizationUri($uri)
    {
        $this->authorize_uri = $uri;
        return $this;
    }

    /**
     * 
     * The uri for the access token.
     *
     * @return string
     *
     */
    public function getAccessTokenUri()
    {
        return $this->access_token_uri;
    }

    /**
     * 
     * Set the full access token uri.
     *
     * @param string $uri
     *
     * @return \Aura\Http\OAuth
     *
     */
    public function setAccessTokenUri($uri)
    {
        $this->access_token_uri = $uri;
        return $this;
    }

    /**
     *
     * Fetch a request token.
     *
     * @param string $callback Callback url.
     *
     * @param array $additional_params
     *
     * @return array 
     *
     * @throws Aura\Http\Exception\OAuthRequestFailure
     *
     */
    public function getRequestToken($callback = 'oob', array $additional_params = array())
    {
        $this->setRequestTokenParams($callback, $additional_params);

        $result = $this->sendRequest($this->getRequestTokenUri(), $additional_params);

        if (200 == $result->getStatusCode()) {
            parse_str($result->getContent(), $return);

            if (! empty($return['oauth_token']) && 
                ! empty($return['oauth_token_secret'])) {

                $this->storage->setTokenType(OAuthStorage::REQUEST);
                $this->storage->setToken($return['oauth_token']);
                $this->storage->setTokenSecret($return['oauth_token_secret']);
                return $return;
            }
        }

        $msg   = 'The request for a request token failed. HTTP status: ' . 
                 $result->getStatusCode() . ' - ' . $result->getStatusText();

        $throw = new Exception\OAuthRequestFailure($msg);
        $throw->setBodyContent($result->getContent());

        throw $throw;
    }

    /**
     * 
     * Fetch user authorization by redirecting the user to the service provider.
     *
     * This method sends the redirect header and calls exit(0);
     *
     * @param array $additional_params Options arguments to append the the 
     * redirect query.
     * 
     */
    public function getUserAuthorization(array $additional_params = array())
    {
        $url = $this->getUserAuthorizationUrl($additional_params);

        $this->response->headers->set('Location', $url);
        $this->response->setStatusCode(302);
        $this->response->setStatusText('Found');

        $this->response->sendHeaders();
        exit(0);
    }

    /**
     * 
     * Exchange the user authorized request token for a user access token.
     *
     * @param string $verifier The verification code.
     *
     * @return array
     *
     * @throws Aura\Http\Exception\OAuthRequestFailure
     * 
     */
    public function getAccessToken($verifier)
    {
        $this->setAccessTokenParams($verifier);

        $result = $this->sendRequest($this->access_token_uri);

        if (200 == $result->getStatusCode()) {
            parse_str($result->getContent(), $return);

            if (! empty($return['oauth_token']) && 
                ! empty($return['oauth_token_secret'])) {

                $this->storage->setTokenType(OAuthStorage::ACCESS);
                $this->storage->setToken($return['oauth_token']);
                $this->storage->setTokenSecret($return['oauth_token_secret']);
                return $return;
            }
        }

        $msg   = 'The request for an access token failed. HTTP status: ' . 
                 $result->getStatusCode() . ' - ' . $result->getStatusText(); 
        $throw = new Exception\OAuthRequestFailure($msg);

        $throw->setBodyContent($result->getContent());

        throw $throw;
    }

    /**
     * 
     * Sign a request and return the oauth_* parameters either as an
     * array or as an authorization header string when the authorization method
     * OAuth::HTTP.
     *
     * @param string $uri
     *
     * @param string $method The HTTP request method.
     *
     * @param array $params The GET query and POST parameters only include the
     * POST parameters if the content-type is `application/x-www-form-urlencoded`.
     *
     * @return string|array
     *
     */
    public function signRequest($uri, $method, array $params = array())
    {
        $oauth_params = array(
            'oauth_consumer_key'     => $this->consumer_key,
            'oauth_signature_method' => $this->signature_method,
            'oauth_timestamp'        => gmdate('U'),
            'oauth_nonce'            => md5(mt_rand() . microtime()),
            'oauth_version'          => '1.0',
            'oauth_token'            => $this->storage->getToken(),
        );

        $oauth_params['oauth_signature'] = 
            $this->signature->generateSignature($uri, $method, $oauth_params + $params);

        if (self::HTTP == $this->authorization_method) {
            return $this->createAuthorizationHeader($oauth_params);
        } else {
            return $oauth_params;
        }
    }

    /**
     * 
     * Fetches the request token, redirects the user to the service provider
     * for authorization and exchanges the request token for an access token.
     *
     * This method sends the redirect header and calls exit(0);
     *
     * @param string $callback The callback url.
     *
     * @param string $verify_code The verification code. Use false when 
     * requesting or authorizing a token. Defaults to false.
     * 
     */
    public function fetchAuthorization($callback = 'oob', $verify_code = false)
    {
        if($this->storage->isRequestToken() && $verify_code) {
            $this->getAccessToken($verify_code);
        } else {
            $this->getRequestToken($callback);
            // getUserAuthorizion will redirect
            $this->getUserAuthorization();
        }
    }

    /**
     * 
     * Send a token request.
     *
     * @param string $uri
     *
     * @param array $additional_params
     *
     * @return \Aura\Http\RequestResponse
     *
     * @throws \Aura\Http\Exception 
     *
     */
    protected function sendRequest($uri, array $additional_params = array())
    {
        $request = $this->request
                        ->reset()
                        ->setUri($uri);
        
        if (self::HTTP == $this->authorization_method) {
            $header = $this->createAuthorizationHeader($this->getParams($uri));
            
            $request->setMethod($this->request_method)
                    ->setHeader('Authorization', $header)
                    ->setContentType('application/x-www-form-urlencoded');
            
            if ($additional_params) {
                $request->setContent($additional_params);
            }

        } else if (self::POST == $this->authorization_method ||
                   self::GET  == $this->authorization_method) {

            $params = $this->getParams($uri);
            $request->setMethod($this->authorization_method);

            if ($additional_params) {
                $request->setContent($params + $additional_params);
            } else {
                $request->setContent($params);
            }

        } else {
            throw new Exception("Invalid authorization method ({$this->authorization_method})");
        }

        $request = $request->send();

        return $request[0];
    }

    /**
     * 
     * Prepare and fetch the required OAuth parameters including any 
     * additional parameters for a request token.
     *
     * @param string $callback
     *
     * @param array $additional_params
     *
     * @return array
     *
     */
    protected function setRequestTokenParams($callback, array $additional_params = array())
    {
        $this->parameters = array(
            'oauth_consumer_key'     => $this->consumer_key,
            'oauth_signature_method' => $this->signature_method,
            'oauth_timestamp'        => gmdate('U'),
            'oauth_nonce'            => md5(mt_rand() . microtime()),
            'oauth_version'          => '1.0',
            'oauth_callback'         => ($callback)
        );
        
        if ($additional_params) {
            $this->parameters += $additional_params;
        }
    }

    /**
     * 
     * Prepare and fetch the required OAuth parameters for an access token.
     *
     * No additional Service Provider specific parameters are allowed 
     * when requesting an Access Token.
     *
     * @param string $verifier The verification code.
     *
     */
    protected function setAccessTokenParams($verifier)
    {
        $this->parameters = array(
            'oauth_consumer_key'     => $this->consumer_key,
            'oauth_signature_method' => $this->signature_method,
            'oauth_timestamp'        => gmdate('U'),
            'oauth_nonce'            => md5(mt_rand() . microtime()),
            'oauth_version'          => '1.0',
            'oauth_token'            => $this->storage->getToken(),
            'oauth_verifier'         => $verifier,
        );
    }

    /**
     * 
     * Get the prepared parameters with signature.
     *
     * @param string $url
     *
     * @return array
     *
     */
    protected function getParams($uri)
    {
        $params                    = $this->parameters;
        $signature                 = $this->signature->generateSignature($uri, 
                                        $this->request_method, $this->parameters);
        $params['oauth_signature'] = $signature;
        $this->parameters          = array();

        return $params;
    }
    
    /**
     * 
     * Create the HTTP authorization header.
     *
     * @param array $params
     *
     * @return string
     * 
     */
    protected function createAuthorizationHeader(array $params)
    {
        $header = 'OAuth ';
        
        foreach ($params as $key => $value) {
            $key     = rawurlencode($key);
            $value   = rawurlencode($value);
            $header .= "{$key}=\"{$value}\", ";
        }

        return trim($header, ', ');
    }
}
