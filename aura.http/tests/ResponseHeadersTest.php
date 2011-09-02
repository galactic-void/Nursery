<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;


class ResponseHeadersTest extends \PHPUnit_Framework_TestCase
{
    public function test__clone()
    {
        $headers = new ResponseHeaders;
        $headers->add('Foo', 'Bar');

        $cloned = clone $headers;

        $this->assertSame(array(), $cloned->getAll());
    }
    
    public function test__get()
    {
        $headers = new ResponseHeaders;
        $headers->add('foo_bar', 'hi');

        $this->assertSame(array('hi'), $headers->{'Foo-Bar'});
    }
    
    public function test__isset()
    {
        $headers = new ResponseHeaders;
        $headers->add('foo_bar', 'hi');

        $this->assertTrue(isset($headers->{'Foo-Bar'}));
        $this->assertFalse(isset($headers->{'Bar-Foo'}));
    }
    
    public function testGetAll()
    {
        $headers = new ResponseHeaders;
        $headers->add('Foo', 'Bar');
        $headers->add('max', 'Powers');

        $expected = array('Foo' => array('Bar'), 'Max' => array('Powers'));

        $this->assertSame($expected, $headers->getAll());
    }
    
    public function testGetIterator()
    {
        $headers = new ResponseHeaders;

        $this->assertInstanceOf('\IteratorAggregate', $headers);
        $this->assertInstanceOf('\ArrayIterator', $headers->getIterator());
    }
    
    public function testAdd()
    {
        $headers = new ResponseHeaders;
        $headers->add('Foo', 'Bar');
        $headers->add('Foo', 'Powers');

        $expected = array('Foo' => array('Bar', 'Powers'));

        $this->assertSame($expected, $headers->getAll());
    }
    
    public function testSet()
    {
        $headers = new ResponseHeaders;

        $headers->set('Foo', 'Bar');
        // should overwrite 'Bar'
        $headers->set('Foo', 'Bars');

        $expected = array('Foo' => array('Bars'));

        $this->assertSame($expected, $headers->getAll());
    }
    
    public function testSetAll()
    {
        $headers  = new ResponseHeaders;
        $set      = array('Foo' => 'Bar', 'Max' => 'Powers');
        $expected = array('Foo' => array('Bar'), 'Max' => array('Powers'));

        $headers->setAll($set);

        $this->assertSame($expected, $headers->getAll());
    }
    
    public function testSanitizeLabel()
    {
        $headers = new ResponseHeaders;

        $headers->add('Fo o!',       'Bar');
        $headers->add('MAX_POwERS', 'helloworld');

        $expected = array('Foo' => array('Bar'), 'Max-Powers' => array('helloworld'));

        $this->assertSame($expected, $headers->getAll());
    }
}