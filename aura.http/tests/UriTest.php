<?php

namespace aura\utility;

use aura\web\Context as WebContext;

// tmp 
require_once '../../../aura.web/src/Context.php';


class UriTest extends \PHPUnit_Framework_TestCase
{
    protected function newUri($uri = null)
    {
        return new Uri(new WebContext($GLOBALS), $uri);
    }

    public function test__toString()
    {
        $_SERVER['HTTP_HOST'] = 'google.com';
        $uri                  = $this->newUri();
        
        ob_start();
        echo $uri;
        $out = ob_get_contents();
        ob_clean();
        
        $this->assertSame('http://google.com', $out);
    }

    public function test__set()
    {
        $uri = $this->newUri();
        
        $this->setExpectedException('\UnexpectedValueException');
        
        $uri->invalid = 'foo';
    }

    public function test__get()
    {
        $uri = $this->newUri('http://usr:pass@example.com:900/path/to.ext?no=where#frag');
        
        $this->assertSame('http',        $uri->scheme);
        $this->assertSame('example.com', $uri->host);
        $this->assertSame(900,           $uri->port);
        $this->assertSame('usr',         $uri->user);
        $this->assertSame('pass',        $uri->pass);
        $path = array(0 => 'path', 1 => 'to');
        $this->assertSame($path,         $uri->path);
        $this->assertSame('ext',         $uri->format);
        $query = array('no' => 'where');
        $this->assertSame($query,        $uri->query);
        $this->assertSame('frag',        $uri->fragment);
        
        $this->setExpectedException('\UnexpectedValueException');
        
        $uri->invalid;
    }

    public function test__clone()
    {
        $uri = $this->newUri('http://example.com/path/to?no=where#frag');
        
        // check properities got changed before cloning
        $uri->set('http://google.com/hello');
        $this->assertSame('google.com',        $uri->host);
        $this->assertSame(array(0 => 'hello'), $uri->path);
        
        $cloned = clone $uri;
        
        $this->assertSame('example.com',                 $cloned->host);
        $this->assertSame(array(0 => 'path', 1 => 'to'), $cloned->path);
        $this->assertSame('no=where',                    $cloned->getQuery());
        $this->assertSame('frag',                        $cloned->fragment);
        
    }

    public function testSetWithFullUri()
    {
        $uri = $this->newUri();
        $uri->set('http://usr:pass@example.com:900/path/to.ext?no=where#frag');
        
        $this->assertSame('http',        $uri->scheme);
        $this->assertSame('example.com', $uri->host);
        $this->assertSame(900,           $uri->port);
        $this->assertSame('usr',         $uri->user);
        $this->assertSame('pass',        $uri->pass);
        $path = array(0 => 'path', 1 => 'to');
        $this->assertSame($path,         $uri->path);
        $this->assertSame('ext',         $uri->format);
        $query = array('no' => 'where');
        $this->assertSame($query,        $uri->query);
        $this->assertSame('frag',        $uri->fragment);
    }
    
    public function testSetWithPartUri()
    {
        $_SERVER['HTTP_HOST'] = 'google.com';
        $uri                  = $this->newUri();
        
        $uri->set('/path/to.ext?no=where#frag');
        
        $this->assertSame('http',   $uri->scheme);
        $this->assertSame('google.com',   $uri->host);
        $this->assertSame(null,   $uri->port);
        $this->assertSame(null,   $uri->user);
        $this->assertSame(null,   $uri->pass);
        $path = array(0 => 'path', 1 => 'to');
        $this->assertSame($path,  $uri->path);
        $this->assertSame('ext',         $uri->format);
        $query = array('no' => 'where');
        $this->assertSame($query, $uri->query);
        $this->assertSame('frag', $uri->fragment);
    }
    
    public function testSetWithNoUri()
    {
        $_SERVER['HTTP_HOST']   = 'google.com';
        $_SERVER['REQUEST_URI'] = '/path/to?a=b';
        $uri                    = $this->newUri('http://foo.com');
        
        $uri->set();
        
        $this->assertSame('http',       $uri->scheme);
        $this->assertSame('google.com', $uri->host);
        $this->assertSame(null,         $uri->port);
        $this->assertSame(null,         $uri->user);
        $this->assertSame(null,         $uri->pass);
        $path = array(0 => 'path', 1 => 'to');
        $this->assertSame($path,        $uri->path);
        $this->assertSame(null,         $uri->format);
        $query = array('a' => 'b');
        $this->assertSame($query,       $uri->query);
        $this->assertSame(null,         $uri->fragment);
    }

    public function testGet()
    {
        $uri = $this->newUri('http://example.com/path/?a=b&c=9');
        
        $this->assertSame('path?a=b&c=9',                    $uri->get());
        $this->assertSame('http://example.com/path?a=b&c=9', $uri->get(true));
        
        $uri->user = 'usr';
        $uri->pass = 'pass';
        
        $this->assertSame('http://usr:pass@example.com/path?a=b&c=9', $uri->get(true));
    }

    public function testQuick()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $uri                  = $this->newUri('http://google.com/');
        
        $this->assertSame('http://example.com/path?a=b&c=9',
                          $uri->quick('path/?a=b&c=9', true));
    }
    
    public function testSetQuery()
    {
        $uri = $this->newUri('http://example.com/?a=b&c=d');
        $uri->setQuery('e=f&g=h');
        
        $this->assertSame('e=f&g=h', $uri->getQuery());
    }
    
    public function testSetQueryPart()
    {
        $uri = $this->newUri('http://example.com/?a=b&c=9');
        $uri->setQueryPart('a', 'c');
        
        $this->assertSame('a=c&c=9', $uri->getQuery());
    }
    
    public function testSetQueryPartByProperity()
    {
        $uri             = $this->newUri('http://example.com/?a=b&c=9');
        $uri->query['a'] = 'c';
        
        $this->assertSame('a=c&c=9', $uri->getQuery());
    }

    public function testGetQuery()
    {
        $uri = $this->newUri('http://example.com/?a=b&c=9');
        
        $this->assertSame('a=b&c=9', $uri->getQuery());
    }

    public function testSetPath()
    {
        $uri = $this->newUri('http://example.com/old/path');
        $uri->setPath('new/path');
        
        $this->assertSame('new/path', $uri->getPath());
    }

    public function testGetPath()
    {
        $uri = $this->newUri('http://example.com/path/to');
        
        $this->assertSame('path/to', $uri->getPath());
    }
}