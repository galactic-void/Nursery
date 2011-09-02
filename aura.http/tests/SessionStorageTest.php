<?php
namespace Aura\Http\Storage;


session_id ("aurahttpstoragephpunit");
session_start();

class SessionStorageTest extends \PHPUnit_Framework_TestCase
{
    const SESSION_ROOT = 'Aura\Http\Storage\Session';

    protected function newSessionStorage()
    {
        unset($_SESSION);
        return new Session();
    }

    public function testImplementsOAuthStorage()
    {
        $this->assertInstanceOf('\Aura\Http\OAuthStorage', $this->newSessionStorage());
    }
    
    public function testIsRequestToken()
    {
        $store = $this->newSessionStorage();
        $store->setTokenType(Session::REQUEST);
        $this->assertTrue($store->isRequestToken());
    }

    public function testIsAccessToken()
    {
        $store = $this->newSessionStorage();
        $store->setTokenType(Session::ACCESS);
        $this->assertTrue($store->isAccessToken());
    }

    public function testSetNamespace()
    {
        $store = $this->newSessionStorage();
        $store->setNamespace('phpunit');
        $store->setToken('token');
        $this->assertSame('token', $_SESSION[self::SESSION_ROOT]['phpunit']['token']);
        $this->assertSame('token', $store->getToken());
    }

    public function testSetToken()
    {
        $store = $this->newSessionStorage();
        $store->setToken('token');
        $this->assertSame('token', $_SESSION[self::SESSION_ROOT][Session::DEFAULT_NAMESPACE]['token']);
        $this->assertSame('token', $store->getToken());
    }
    
    public function testSetTokenSecret()
    {
        $store = $this->newSessionStorage();
        $store->setTokenSecret('secret');
        $this->assertSame('secret', $_SESSION[self::SESSION_ROOT][Session::DEFAULT_NAMESPACE]['token_secret']);
        $this->assertSame('secret', $store->getTokenSecret());
    }

    public function testDelete()
    {
        $store = $this->newSessionStorage();
        $store->setToken('token');
        $store->setTokenSecret('secret');
        
        // check the token exists in the Session::DEFAULT_NAMESPACE namespace
        $this->assertTrue(isset($_SESSION[self::SESSION_ROOT][Session::DEFAULT_NAMESPACE]));

        $store->setNamespace('phpunit');
        $store->setToken('token');
        $store->setTokenSecret('secret');

        // check the token exists in the phpunit namespace
        $this->assertTrue(isset($_SESSION[self::SESSION_ROOT]['phpunit']));

        // we are still in phpunit namespace
        $store->delete();
        $this->assertFalse(isset($_SESSION[self::SESSION_ROOT]['phpunit']));

        // test the default namespace
        $store->setNamespace(Session::DEFAULT_NAMESPACE);

        $store->delete();
        $this->assertFalse(isset($_SESSION[self::SESSION_ROOT][Session::DEFAULT_NAMESPACE]));
    }
}