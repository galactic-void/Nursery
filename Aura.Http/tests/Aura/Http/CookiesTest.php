<?php

namespace Aura\Http;

use Aura\Http\Factory\Cookie as CookieFactory;

class CookiesTest extends \PHPUnit_Framework_TestCase
{
    protected function newCookies()
    {
        return new Cookies(new CookieFactory);
    }

    protected function newCookie(
        $name, 
        $value    = null, 
        $expire   = null, 
        $path     = null, 
        $domain   = null, 
        $secure   = false, 
        $httponly = true
    )
    {
        return new Cookie(
            $name, 
            $value, 
            $expire, 
            $path, 
            $domain, 
            $secure, 
            $httponly
        );
    }

    public function test__clone()
    {
        $cookies = $this->newCookies();
        $cookies->set('Foo', array());

        $cloned = clone $cookies;

        $this->assertSame([], $cloned->getAll());
    }
    
    public function test__get()
    {
        $cookies = $this->newCookies();
        $cookies->set('foo_bar', array());

        $this->assertEquals( $this->newCookie('foo_bar'), $cookies->foo_bar);
    }
    
    public function test__isset()
    {
        $cookies = $this->newCookies();
        $cookies->set('foo_bar', array());

        $this->assertTrue(isset($cookies->foo_bar));
        $this->assertFalse(isset($cookies->Bar_Foo));
    }
    
    public function test__unset()
    {
        $cookies = $this->newCookies();
        $cookies->set('foo_bar', array());

        $this->assertTrue(isset($cookies->foo_bar));

        unset($cookies->foo_bar);

        $this->assertFalse(isset($cookies->foo_bar));
    }
    
    public function testCount()
    {
        $cookies = $this->newCookies();

        $cookies->set('Foo', array());
        $cookies->set('max', array('value' => 'hi'));

        $this->assertEquals(2, count($cookies));
    }
    
    public function testGetIterator()
    {
        $cookies = $this->newCookies();

        $this->assertInstanceOf('\IteratorAggregate', $cookies);
        $this->assertInstanceOf('\ArrayIterator', $cookies->getIterator());
    }
    
    public function testGetAll()
    {
        $cookies = $this->newCookies();
        $cookies->set('Foo', array());
        $cookies->set('max', array('value' => 'hi'));

        $expected = [
            'Foo' => $this->newCookie('Foo'),
            'max' => $this->newCookie('max', 'hi'),
        ];

        $this->assertEquals($expected, $cookies->getAll());
    }
    
    public function testSetAll()
    {
        $cookies = $this->newCookies();
        $set     = array(
            'foo' => array('value' => 'bar', 'path' => '/example'),
            'max' => array('value' => 'powers'));

        $cookies->setAll($set);

        $expected = [
            'foo' => $this->newCookie('foo', 'bar', null, '/example'),
            'max' => $this->newCookie('max', 'powers'),
        ];

        $this->assertEquals($expected, $cookies->getAll());
    }
    
    public function testAddWithCookie()
    {
        $cookies = $this->newCookies(null);
        $cookie  = new Cookie('Foo', 'Bar', null, null, null, null, null);

        $cookies->set($cookie);

        $this->assertSame($cookie, $cookies->Foo);
    }
    
    public function testSetFromString()
    {
        $cookies  = $this->newCookies();
        $set      = 'name=value; Expires=Wed, 09 Jun 2021 10:18:14 GMT;httponly';

        $cookies->setFromString($set);

        $expected = ['name' => $this->newCookie('name', 'value', 'Wed, 09 Jun 2021 10:18:14 GMT')];
        $this->assertEquals($expected, $cookies->getAll());
    }

}