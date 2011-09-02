<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;


class ResponseCookiesTest extends \PHPUnit_Framework_TestCase
{
    protected $base = array(
        'value'    => null,
        'expire'   => 0,
        'path'     => null,
        'domain'   => null,
        'secure'   => false,
        'httponly' => true,
    );
       
    public function test__clone()
    {
        $cookies = new ResponseCookies;
        $cookies->set('Foo', array());

        $cloned = clone $cookies;

        $this->assertSame(array(), $cloned->getAll());
    }
    
    public function test__get()
    {
        $cookies = new ResponseCookies;
        $cookies->set('foo_bar', array());

        $this->assertSame($this->base, $cookies->foo_bar);
    }
    
    public function test__isset()
    {
        $cookies = new ResponseCookies;
        $cookies->set('foo_bar', array());

        $this->assertTrue(isset($cookies->foo_bar));
        $this->assertFalse(isset($cookies->Bar_Foo));
    }
    
    public function testGetIterator()
    {
        $cookies = new ResponseCookies;

        $this->assertInstanceOf('\IteratorAggregate', $cookies);
        $this->assertInstanceOf('\ArrayIterator', $cookies->getIterator());
    }
    
    public function testGetAll()
    {
        $cookies = new ResponseCookies;
        $cookies->set('Foo', array());
        $cookies->set('max', array('value' => 'hi'));

        $max_exp  = array('value' => 'hi') + $this->base;
        $expected = array('Foo' => $this->base, 'max' => $max_exp);

        $this->assertEquals($expected, $cookies->getAll());
    }
    
    public function testSetAll()
    {
        $cookies = new ResponseCookies;
        $set     = array(
            'foo' => array('value' => 'bar', 'path' => '/example'),
            'max' => array('value' => 'powers'));

        $cookies->setAll($set);

        $expected['foo'] = array('value' => 'bar', 'path' => '/example') + $this->base;
        $expected['max'] = array('value' => 'powers') + $this->base;

        $this->assertEquals($expected, $cookies->getAll());
    }
    
    public function testSetFromString()
    {
        $cookies  = new ResponseCookies;
        $set      = 'name=value; Expires=Wed, 09 Jun 2021 10:18:14 GMT;httponly';

        $cookies->setFromString($set);

        $expected['name'] = array('value' => 'value') + $this->base;

        $this->assertEquals($expected, $cookies->getAll());
    }

}
