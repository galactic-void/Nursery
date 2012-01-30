<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Request;

use Aura\Http\Factory\Header as HeaderFactory;
use Aura\Http\Header as Header;

class RequestHeadersTest extends \PHPUnit_Framework_TestCase
{
    protected function newHeaders()
    {
        return new Headers(new HeaderFactory);
    }

    protected function newHeader($label, $value)
    {
        return new Header($label, $value);
    }

    public function test__clone()
    {
        $headers = $this->newHeaders();
        $headers->add('Foo', 'Bar');

        $cloned = clone $headers;

        $this->assertSame(array(), $cloned->getAll());
    }
    
    public function test__get()
    {
        $headers = $this->newHeaders();
        $headers->add('foo_bar', 'hi');

        $expected = $this->newHeader('Foo-Bar', 'hi');

        $this->assertEquals($expected, $headers->{'Foo-Bar'});
    }
    
    public function test__isset()
    {
        $headers = $this->newHeaders();
        $headers->add('foo_bar', 'hi');

        $this->assertTrue(isset($headers->{'Foo-Bar'}));
        $this->assertFalse(isset($headers->{'Bar-Foo'}));
    }
    
    public function testGetAll()
    {
        $headers = $this->newHeaders();
        $headers->add('Foo', 'Bar');
        $headers->add('max', 'Powers');

        $expected = [
            'Foo' => [$this->newHeader('Foo', 'Bar')],
            'Max' => [$this->newHeader('Max', 'Powers')],
        ];

        $this->assertEquals($expected, $headers->getAll());
    }
    
    public function testGetIterator()
    {
        $headers = $this->newHeaders();

        $this->assertInstanceOf('\IteratorAggregate', $headers);
        $this->assertInstanceOf('\ArrayIterator', $headers->getIterator());
    }
    
    public function testAdd()
    {
        $headers = $this->newHeaders();
        $headers->add('Foo', 'Bar');
        $headers->add('Foo', 'Powers');
        
        $expected = [
            'Foo' => [
                $this->newHeader('Foo', 'Bar'),
                $this->newHeader('Foo', 'Powers')],
        ];

        $this->assertEquals($expected, $headers->getAll());
    }
    
    public function testSet()
    {
        $headers = $this->newHeaders();

        $headers->set('Foo', 'Bar');
        // should overwrite 'Bar'
        $headers->set('Foo', 'Bars');

        $expected = [
            'Foo' => [$this->newHeader('Foo', 'Bars')],
        ];

        $this->assertEquals($expected, $headers->getAll());
    }
    
    public function testSetAll()
    {
        $headers  = $this->newHeaders();
        $set      = array('Foo' => 'Bar', 'Max' => 'Powers');
        $expected = [
            'Foo' => [$this->newHeader('Foo', 'Bar')],
            'Max' => [$this->newHeader('Max', 'Powers')],
        ];

        $headers->setAll($set);

        $this->assertEquals($expected, $headers->getAll());
    }
    
    public function testSanitizeLabel()
    {
        $headers = $this->newHeaders();

        $headers->add('Fo o!',       'Bar');
        $headers->add('MAX_POwERS', 'helloworld');

        $expected = [
            'Foo'        => [$this->newHeader('Foo', 'Bar')],
            'Max-Powers' => [$this->newHeader('Max-Powers', 'helloworld')],
        ];

        $this->assertEquals($expected, $headers->getAll());
    }
}