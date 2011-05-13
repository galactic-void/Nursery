<?php

namespace aura\http;

// tmp
//require_once '../src/ResourceResponse.php';
//require_once '../../../aura.http/src/MimeUtility.php';
///require_once '../../aura.utility/src/Uri.php';

class ResourceResponseTest extends \PHPUnit_Framework_TestCase
{
    protected function newResourceResponse()
    {
        return new ResourceResponse(new MimeUtility());
    }

    public function test__clone()
    {
        $rr = $this->newResourceResponse();
        
        $rr->setContent('Hi');
        $rr->setHeader('foo', 'bar');
        $rr->setCookie('name');
        $rr->setStatusCode(222);
        $rr->setStatusText('I\'m a teapot');
        $rr->setVersion('1.0');
        
        $clone = clone $rr;
        
        $this->assertEmpty($clone->getContent());
        $this->assertEmpty($clone->getHeader(null));
        $this->assertEmpty($clone->getCookie(null));
        $this->assertEquals(200, $clone->getStatusCode());
        $this->assertEmpty($clone->getStatusText());
        $this->assertEquals('1.1', $clone->getVersion());
    }

    public function testSetContent()
    {
        $rr = $this->newResourceResponse();
        
        $rr->setContent('Hello world!!');
        $actual = $rr->getContent();
        
        $this->assertSame('Hello world!!', $actual);
    }

    public function testGzipedSetContent()
    {
        $rr      = $this->newResourceResponse();
        $content = file_get_contents(__DIR__ . '/_files/gziphttp');
        
        $rr->setHeader('Content-Encoding', 'gzip');
        $rr->setContent($content);
        $actual = $rr->getContent();
        
        $this->assertSame('Hello gzip world', $actual);
    }

    public function testInvalidGzipedSetContent()
    {
        $rr      = $this->newResourceResponse();
        $content = file_get_contents(__DIR__ . '/_files/gziphttp');
        
        $rr->setHeader('Content-Encoding', 'gzip');
        $this->setExpectedException('aura\http\Exception_UnableToUncompressContent');
        $rr->setContent('invalid' . $content);
    }

    public function testInvalidInflatedSetContent()
    {
        $rr      = $this->newResourceResponse();
        $content = file_get_contents(__DIR__ . '/_files/deflatehttp');
        
        $rr->setHeader('Content-Encoding', 'inflate');
        $this->setExpectedException('aura\http\Exception_UnableToUncompressContent');
        $rr->setContent('invalid' . $content);
    }

    public function testInflatedSetContent()
    {
        $rr      = $this->newResourceResponse();
        $content = file_get_contents(__DIR__ . '/_files/deflatehttp');
        
        $rr->setHeader('Content-Encoding', 'inflate');
        $rr->setContent($content);
        $actual = $rr->getContent();
        
        $this->assertSame('Hello deflated world', $actual);
    }

    public function testParseAndSetCookie()
    {
        $cookie = 'name=foobar; Domain=.foo.com; Path=/; Expires=Wed, 13-Jan-2021 22:23:01 GMT; Secure; HttpOnly';
        $rr     = $this->newResourceResponse(); 
        
        $rr->parseAndSetCookie($cookie);
        
        $actual   = $rr->getCookie('name');
        $expected = array(
            'value'    => 'foobar',
            'expires'  => 'Wed, 13-Jan-2021 22:23:01 GMT',
            'path'     => '/',
            'domain'   => '.foo.com',
            'secure'   => true,
            'httponly' => true
        );
        
        $this->assertSame($expected, $actual);
    }
}