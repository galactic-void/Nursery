<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Signature;

/**
 * 
 * Create a HMAC SHA1 OAuth signature.
 * 
 * @package aura.http
 * 
 */
 class HmacSha1 extends \Aura\Http\OAuthSignature
{
    /**
     * 
     * Sign and base64 encode the signature base string.
     *
     * @param string $sbs Signature base string.
     *
     * @return string
     *
     */
    protected function sign($sbs)
    {
        $key = rawurlencode($this->consumer_secret) . '&' .
               rawurlencode($this->storage->getTokenSecret());
        
        return base64_encode(hash_hmac('sha1', $sbs, $key, true));
    }
}