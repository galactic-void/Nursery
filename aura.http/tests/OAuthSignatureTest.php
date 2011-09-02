<?php

namespace Aura\Http;


require_once 'MockOAuthSignature.php';


class OAuthSignatureTest extends \PHPUnit_Framework_TestCase
{
    protected function newSignature()
    {
        return new MockOAuthSignature;
    }

    public function testSetStorageReturnsOAuthSignature()
    {
        $sign    = new MockOAuthSignature;
        $storage = $this->getMock('\Aura\Http\OAuthStorage');
        $return  = $sign->setStorage($storage);

        $this->assertInstanceOf('\Aura\Http\OAuthSignature', $return);
    }

    public function testSetConsumerSecretReturnsOAuthSignature()
    {
        $sign   = new MockOAuthSignature;
        $return = $sign->setConsumerSecret('secret');

        $this->assertInstanceOf('\Aura\Http\OAuthSignature', $return);
    }

    public function testSetAuthorizationMethodReturnsOAuthSignature()
    {
        $sign   = new MockOAuthSignature;
        $return = $sign->setAuthorizationMethod(OAuth::HTTP);
        
        $this->assertInstanceOf('\Aura\Http\OAuthSignature', $return);
    }

    public function testGenerateSignature()
    {
        $params = array('key' => 'value', 'key2' => array('abc', 'xyz'));
        $sign   = new MockOAuthSignature;

        $sign->setAuthorizationMethod(OAuth::POST);
        
        $expected = $sign->generateSignature('http://example.com', OAuth::GET, $params);
        $actual   = 'POST&http%3A%2F%2Fexample.com&key%3Dvalue%26key2%3Dabc%26key2%3Dxyz';

        $this->assertSame($expected, $actual);
    }

    public function testGenerateSignatureUsingHeaderAuthorization()
    {
        $params = array('key' => 'value', 'key2' => array('abc', 'xyz'));
        $sign   = new MockOAuthSignature;

        $sign->setAuthorizationMethod(OAuth::HTTP);
        
        $expected = $sign->generateSignature('http://example.com', OAuth::POST, $params);
        $actual   = 'POST&http%3A%2F%2Fexample.com&key%3Dvalue%26key2%3Dabc%26key2%3Dxyz';

        $this->assertSame($expected, $actual);
    }

    public function testGenerateSignatureWithDefaultHttpPort()
    {
        $params = array('key' => 'value', 'key2' => array('abc', 'xyz'));
        $sign   = new MockOAuthSignature;

        $sign->setAuthorizationMethod(OAuth::POST);
        
        $expected = $sign->generateSignature('http://example.com:80', OAuth::GET, $params);
        $actual   = 'POST&http%3A%2F%2Fexample.com&key%3Dvalue%26key2%3Dabc%26key2%3Dxyz';

        $this->assertSame($expected, $actual);
    }

    public function testGenerateSignatureWithHttpPort()
    {
        $params = array('key' => 'value', 'key2' => array('abc', 'xyz'));
        $sign   = new MockOAuthSignature;

        $sign->setAuthorizationMethod(OAuth::POST);
        
        $expected = $sign->generateSignature('http://example.com:8000', OAuth::GET, $params);
        $actual   = 'POST&http%3A%2F%2Fexample.com%3A8000&key%3Dvalue%26key2%3Dabc%26key2%3Dxyz';

        $this->assertSame($expected, $actual);
    }

    public function testGenerateSignatureWithDefaultHttpsPort()
    {
        $params = array('key' => 'value', 'key2' => array('abc', 'xyz'));
        $sign   = new MockOAuthSignature;

        $sign->setAuthorizationMethod(OAuth::POST);
        
        $expected = $sign->generateSignature('https://example.com:443', OAuth::GET, $params);
        $actual   = 'POST&https%3A%2F%2Fexample.com&key%3Dvalue%26key2%3Dabc%26key2%3Dxyz';

        $this->assertSame($expected, $actual);
    }

    public function testGenerateSignatureWithHttpsPort()
    {
        $params = array('key' => 'value', 'key2' => array('abc', 'xyz'));
        $sign   = new MockOAuthSignature;

        $sign->setAuthorizationMethod(OAuth::POST);
        
        $expected = $sign->generateSignature('https://example.com:8000', OAuth::GET, $params);
        $actual   = 'POST&https%3A%2F%2Fexample.com%3A8000&key%3Dvalue%26key2%3Dabc%26key2%3Dxyz';

        $this->assertSame($expected, $actual);
    }
}
