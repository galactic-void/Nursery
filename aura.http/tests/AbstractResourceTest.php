<?php

namespace aura\http;

use aura\web\Context as WebContext;
use aura\http\MimeUtility as MimeUtility;
use aura\http\Uri as Uri;

function function_exists($func)
{
    return AbstractResourceTest::$function_exists;
}

class AbstractResourceTest extends \PHPUnit_Framework_TestCase
{
    public static $function_exists = true;
    
    protected $uri;
    
    public function setUp()
    {
        parent::setUp();
        $this->uri = new Uri(new WebContext($GLOBALS), 'http://google.com');
    }


    protected function newResource($opts = array())
    {
        return $this->getMockForAbstractClass('\aura\http\AbstractResource', 
            array(new Uri(new WebContext($GLOBALS)), new MimeUtility, $opts));
    }

    public function test__clone()
    {
        $opts = array(
            'charset'         => 'utf-8',
            'content_type'    => 'text/text',
            'max_redirects'   => 42,
            'proxy'           => 'http://example.com',
            'timeout'         => 4.2,
            'user_agent'      => 'aura/tests',
            'version'         => '1.0',
            'ssl_cafile'      => 'cafile',
            'ssl_capath'      => 'ca/path',
            'ssl_local_cert'  => '/to/cert',
            'ssl_passphrase'  => 'pass',
            'ssl_verify_peer' => true,
        );
        
        $res     = $this->newResource($opts);
        $default = $res->getOptions();
        $cres    = clone $res;
        $cloned  = $cres->getOptions();
        
        $this->assertEquals($default, $cloned);
    }

    public function testSetBasicAuth()
    {
        $res      = $this->newResource();
        $set      = $res->setBasicAuth('usr', 'pass');
        $actual   = $this->readAttribute($res, 'headers');
        $expected = array('Authorization' => 'Basic dXNyOnBhc3M=');
        
        $this->assertSame($expected, $actual);
        $this->assertInstanceOf('\aura\http\Resource', $set);
        
        $res->setBasicAuth(false, false);
        $actual = $this->readAttribute($res, 'headers');
        $this->assertEquals(array(), $actual);
    }

    public function testBasicAuthColonException()
    {
        $res = $this->newResource();
        
        $this->setExpectedException('\aura\http\Exception');
        $res->setBasicAuth('invalid:handle', 'pass');
    }

    public function testSetCharset()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'charset');
        
        // default is utf-8 
        $this->assertSame('utf-8', $actual);
        
        $set    = $res->setCharset('utf-7');
        $actual = $this->readAttribute($res, 'charset');
        
        $this->assertSame('utf-7', $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetContent()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'content');
        
        // default is null
        $this->assertNull($actual);
        
        $set    = $res->setContent('hello world');
        $actual = $this->readAttribute($res, 'content');
        
        $this->assertSame('hello world', $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }
    
    public function testSetContentType()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'content_type');
        
        // default is null
        $this->assertNull($actual);
        
        $set    = $res->setContentType('text/plain');
        $actual = $this->readAttribute($res, 'content_type');
        
        $this->assertSame('text/plain', $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetCookie()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'cookies');
        
        // default
        $this->assertSame(array(), $actual);
        
        $set    = $res->setCookie("test\r\n", "hello world");
        $actual = $this->readAttribute($res, 'cookies');
        $expect = array('test' => 'hello world');
        
        $this->assertSame($expect, $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetCookieArrayValue()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'cookies');
        $value  = array('value' => 'hello world');
        
        $set    = $res->setCookie("test\r\n", $value);
        $actual = $this->readAttribute($res, 'cookies');
        $expect = array('test' => 'hello world');
        
        $this->assertSame($expect, $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetCookies()
    {
        $cookies = array(
            'cookie1' => 'value1',
            'cookie2' => 'values2'
        );
        
        $res    = $this->newResource();
        $set    = $res->setCookies($cookies);
        $actual = $this->readAttribute($res, 'cookies');
        
        $this->assertSame($cookies, $actual);
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetHeader()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'headers');
        
        // default is array()
        $this->assertEmpty($actual);
        
        $set    = $res->setHeader('key', 'value');
        $actual = $this->readAttribute($res, 'headers');
        $this->assertInstanceOf('\aura\http\Resource', $set);
        
        $this->assertSame(array('Key' => 'value'), $actual);
        
        // default replaces 
        $res->setHeader('key', 'value2');
        $actual = $this->readAttribute($res, 'headers');
        $this->assertSame(array('Key' => 'value2'), $actual);
        
        // send multi headers
        $res->setHeader('key', 'value', false);
        $actual = $this->readAttribute($res, 'headers');
        $this->assertSame(array('Key' => array('value2', 'value')), $actual);
        
        // passing null deletes header
        $res->setHeader('key', null);
        $this->assertEmpty($this->readAttribute($res, 'headers'));
        
        // passing false deletes header
        $res->setHeader('key', 'value');
        $res->setHeader('key', false);
        $this->assertEmpty($this->readAttribute($res, 'headers'));
        
        $this->setExpectedException('\aura\http\Exception');
        $res->setHeader('cookie', 'no');
    }

    public function testSetHeaderSpecialMethods()
    {
        $res    = $this->newResource();
        
        $res->setHeader('content-type', 'text/js');
        $actual = $this->readAttribute($res, 'content_type');
        $this->assertSame('text/js', $actual);
        
        $res->setHeader('http', '1.0');
        $actual = $this->readAttribute($res, 'version');
        $this->assertSame('1.0', $actual);
        
        $res->setHeader('referer', 'http://example.com');
        $actual = $this->readAttribute($res, 'referer');
        $this->assertSame('example.com', $actual->host);
        
        $res->setHeader('user-agent', 'aura/Resource 1.0');
        $actual = $this->readAttribute($res, 'user_agent');
        $this->assertSame('aura/Resource 1.0', $actual);
    }

    public function testSetMaxRedirects()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'max_redirects');
        
        // default is null
        $this->assertNull($actual);
        
        $set    = $res->setMaxRedirects(2);
        $actual = $this->readAttribute($res, 'max_redirects');
        
        $this->assertSame(2, $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testResetingSetMaxRedirects()
    {
        $res    = $this->newResource();
        
        $res->setMaxRedirects(2);
        $actual = $this->readAttribute($res, 'max_redirects');
        
        // check its been set
        $this->assertSame(2, $actual);
        
        $res->setMaxRedirects(null);
        $actual = $this->readAttribute($res, 'max_redirects');
        
        $this->assertNull($actual);
    }

    public function testSetMethod()
    {
        $res     = $this->newResource();
        $actual  = $this->readAttribute($res, 'method');
        // default
        $this->assertSame('GET', $actual);
        
        $allowed = array(
            'get',
            'POST',
            'PUT',
            'DELETE',
            'TRACE',
            'OPTIONS',
            'TRACE',
            'COPY',
            'LOCK',
            'MKCOL',
            'MOVE',
            'PROPFIND',
            'PROPPATCH',
            'UNLOCK'
        );
        
        foreach ($allowed as $method) {
            $set    = $res->setMethod($method);
            $actual = $this->readAttribute($res, 'method');
            $this->assertSame(strtoupper($method), $actual);
        }
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetMethodException()
    {
        $this->setExpectedException('\aura\http\Exception_UnknownMethod');
        $res    = $this->newResource();
        $res->setMethod('FOO');
    }

    public function testSetProxy()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'proxy');
        
        // default is null
        $this->assertNull($actual);
        
        $set    = $res->setProxy('http://example.com');
        $actual = $this->readAttribute($res, 'proxy');
        
        $this->assertSame('example.com', $actual->host);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetProxyWithUri()
    {
        $res    = $this->newResource();
        $set    = $res->setProxy($this->uri);
        $actual = $this->readAttribute($res, 'proxy');
        
        $this->assertSame('google.com', $actual->host);
    }

    public function testSetReferer()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'referer');
        
        // default is null
        $this->assertNull($actual);
        
        $set    = $res->setReferer('http://example.com');
        $actual = $this->readAttribute($res, 'referer');
        
        $this->assertSame('example.com', $actual->host);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetRefererWithUri()
    {
        $res    = $this->newResource();
        $set    = $res->setReferer($this->uri);
        $actual = $this->readAttribute($res, 'referer');
        
        $this->assertSame('google.com', $actual->host);
    }

    public function testSetTimeout()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'timeout');
        
        // default is 0
        $this->assertSame(0.0, $actual);
        
        $set    = $res->setTimeout(2.5);
        $actual = $this->readAttribute($res, 'timeout');
        
        $this->assertSame(2.5, $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetUri()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'resource_uri');
        
        // default is null
        $this->assertNull($actual);
        
        $set    = $res->setUri('http://example.com');
        $actual = $this->readAttribute($res, 'resource_uri');
        
        $this->assertSame('example.com', $actual->host);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetUriWithUri()
    {
        $res    = $this->newResource();
        $set    = $res->setUri($this->uri);
        $actual = $this->readAttribute($res, 'resource_uri');
        
        $this->assertSame('google.com', $actual->host);
    }

    public function testSetUserAgent()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'user_agent');
        
        // default is null
        $this->assertNull($actual);
        
        $set    = $res->setUserAgent('AuraResource/1.0');
        $actual = $this->readAttribute($res, 'user_agent');
        
        $this->assertSame('AuraResource/1.0', $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetGzip()
    {
        self::$function_exists = true;
        
        $res      = $this->newResource();
        $set      = $res->setGzip();
        $actual   = $this->readAttribute($res, 'accept_encoding');
        $expected = array('gzip' => 'gzip');
        
        $this->assertSame($expected, $actual);
        $this->assertInstanceOf('\aura\http\Resource', $set);
        
        $res->setGzip(false);
        
        $actual = $this->readAttribute($res, 'accept_encoding');
        
        $this->assertSame(array(), $actual);
    }

    public function testSetGzipWithoutZlib()
    {
        $this->setExpectedException('\aura\http\Exception');
        
        self::$function_exists = false;
        $res                   = $this->newResource();
        $set                   = $res->setGzip();
    }

    public function testSetDeflate()
    {
        self::$function_exists = true;
        
        $res      = $this->newResource();
        $set      = $res->setDeflate();
        $actual   = $this->readAttribute($res, 'accept_encoding');
        $expected = array('deflate' => 'deflate');
        
        $this->assertSame($expected, $actual);
        $this->assertInstanceOf('\aura\http\Resource', $set);
        
        $res->setDeflate(false);
        
        $actual = $this->readAttribute($res, 'accept_encoding');
        
        $this->assertSame(array(), $actual);
    }

    public function testSetDeflateWithoutZlib()
    {
        $this->setExpectedException('\aura\http\Exception');
        
        self::$function_exists = false;
        $res                   = $this->newResource();
        $set                   = $res->setDeflate();
    }

    public function testSetVersion()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'version');
        
        // default
        $this->assertSame('1.1', $actual);
        
        $set    = $res->setVersion('1.0');
        $actual = $this->readAttribute($res, 'version');
        
        $this->assertSame('1.0', $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetVersionException()
    {
        $this->setExpectedException('\aura\http\Exception_UnknownVersion');
        $res    = $this->newResource();
        $res->setVersion('10');
    }

    public function testSetSslVerifyPeer()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'ssl_verify_peer');
        
        // default
        $this->assertFalse($actual);
        
        $set    = $res->setSslVerifyPeer(true);
        $actual = $this->readAttribute($res, 'ssl_verify_peer');
        
        $this->assertTrue($actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetSslCafile()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'ssl_cafile');
        
        // default
        $this->assertNull($actual);
        
        $set    = $res->setSslCafile('cafile.ext');
        $actual = $this->readAttribute($res, 'ssl_cafile');
        
        $this->assertSame('cafile.ext', $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetSslCapath()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'ssl_capath');
        
        // default
        $this->assertNull($actual);
        
        $set    = $res->setSslCapath('/path/to');
        $actual = $this->readAttribute($res, 'ssl_capath');
        
        $this->assertSame('/path/to', $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetSslLocalCert()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'ssl_local_cert');
        
        // default
        $this->assertNull($actual);
        
        $set    = $res->setSslLocalCert('/path/to/cert.ext');
        $actual = $this->readAttribute($res, 'ssl_local_cert');
        
        $this->assertSame('/path/to/cert.ext', $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testSetSslPassphrase()
    {
        $res    = $this->newResource();
        $actual = $this->readAttribute($res, 'ssl_passphrase');
        
        // default
        $this->assertNull($actual);
        
        $set    = $res->setSslPassphrase('pass');
        $actual = $this->readAttribute($res, 'ssl_passphrase');
        
        $this->assertSame('pass', $actual);
        
        $this->assertInstanceOf('\aura\http\Resource', $set);
    }

    public function testFetchWithoutUriException()
    {
        $this->setExpectedException('\aura\http\Exception');
        
        $this->newResource()->fetch();
    }

    public function testFetch()
    {
        $result = $this->fetchResourceMock()->fetch();
        
        $this->assertEquals(2, $result->count());
        $this->assertEquals('hello world', $result[0]->getContent());
        $this->assertEquals('Thu, 12 May 2011 21:04:09 GMT', $result[0]->getHeader('Date')); 
        $this->assertEquals('Thu, 12 May 2011 21:04:08 GMT', $result[1]->getHeader('Date'));
        $this->assertEquals(200, $result[0]->getStatusCode());
        $this->assertEquals(301, $result[1]->getStatusCode());
    }

    public function testFetchGetWithArrayContent()
    {
        $res = $this->newResource();
        
        $res->expects($this->once())
            ->method('adapterFetch')
            ->will($this->returnCallback(array($this, 'getArrayContentCallback')));
        
        $res->setUri('http://example.com')
            ->setContent(array('hello', 'world'))
            ->fetch();
    }
    
    public function getArrayContentCallback($uri, array $headers, $content)
    {
        $this->assertEquals('http://example.com?0=hello&1=world', $uri);
        
        return array(array('HTTP/1.1 200 Ok'), null);
    }

    public function testFetchPostWithArrayContent()
    {
        $res = $this->newResource();
        
        $res->expects($this->once())
            ->method('adapterFetch')
            ->will($this->returnCallback(array($this, 'postArrayContentCallback')));
        
        $res->setUri('http://example.com')
            ->setMethod(\aura\http\Resource::METHOD_POST)
            ->setContent(array('hello', 'world'))
            ->fetch();
    }
    
    public function postArrayContentCallback($uri, array $headers, $content)
    {
        $expect_headers = array(
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'Content-Length: 15'
        );
        
        $this->assertSame($expect_headers, $headers);
        $this->assertSame('0=hello&1=world', $content);
        
        return array(array('HTTP/1.1 200 Ok'), null);
    }

    public function testFetchPostWithStringContent()
    {
        $res = $this->newResource();
        
        $res->expects($this->once())
            ->method('adapterFetch')
            ->will($this->returnCallback(array($this, 'postStringContentCallback')));
        
        $res->setUri('http://example.com')
            ->setMethod(\aura\http\Resource::METHOD_POST)
            ->setContent('hello world')
            ->setContentType('text/text')
            ->fetch();
    }
    
    public function postStringContentCallback($uri, array $headers, $content)
    {
        $expect_headers = array(
            'Content-Type: text/text; charset=utf-8',
            'Content-Length: 11'
        );
        
        $this->assertSame($expect_headers, $headers);
        $this->assertSame('hello world', $content);
        
        return array(array('HTTP/1.1 200 Ok'), null);
    }
    
    public function testFetchEmptyResultException()
    {
        $this->setExpectedException('\aura\http\Exception_EmptyResponse');
        
        $res = $this->newResource();
        
        $res->expects($this->once())
            ->method('adapterFetch')
            ->will($this->returnValue(array(array(), null)));
        
        $res->setUri('example.com');
        $res->fetch();
    }
    
    public function testFetchSendingHeadersAndCookies()
    {
        self::$function_exists = true;
        $res = $this->newResource();
        
        $res->expects($this->once())
            ->method('adapterFetch')
            ->will($this->returnCallback(array($this, 'sendingHeadersAndCookiesCallback')));
        
        $res->setUri('http://example.com')
            ->setHeader('X-foo', 'bar')
            ->setGzip()
            ->setUserAgent('Aura/Testing 1.0')
            ->setReferer('http://example.com/from')
            ->setContentType('text/html')
            ->setCookie('name', 'foo;domain=.example.com;expires=Tue Nov 8 16:04:08 2011;path=/; HttpOnly');
        
        $res->fetch();
        
    }
    
    public function sendingHeadersAndCookiesCallback($uri, array $headers, $content)
    {
        $expect = array(
            'X-Foo: bar',
            'Accept-Encoding: gzip',
            'User-Agent: Aura/Testing 1.0',
            'Referer: http://example.com/from',
            'Cookie: name=foo%3Bdomain%3D.example.com%3Bexpires%3DTue+Nov+8+16%3A04%3A08+2011%3Bpath%3D%2F%3B+HttpOnly',
        );
        
        $this->assertSame($expect, $headers);
        
        return array(array('HTTP/1.1 200 Ok'), null);
    }
    
    public function testFetchRaw()
    {
        $result = $this->fetchResourceMock()->fetchRaw();
        $expect = file_get_contents(__DIR__ . '/_files/fetchRaw');
        
        $this->assertEquals($expect, $result);
    }
    
    protected function fetchResourceMock()
    {
        $headers = array(
        	'HTTP/1.1 301 Moved',
            'Server: nginx',
            'Date: Thu, 12 May 2011 21:04:08 GMT',
            'Content-Type: text/html; charset=utf-8',
            'Location: http://example.com/moved',
            'Set-Cookie: name=foo;domain=.example.com;expires=Tue Nov 8 16:04:08 2011;path=/; HttpOnly',
        	'HTTP/1.1 200 Ok',
            'Server: nginx',
            'Date: Thu, 12 May 2011 21:04:09 GMT',
            'Content-Type: text/text; charset=utf-8',
        );
        $return  = array($headers, 'hello world');
        $res     = $this->newResource();
        
        $res->expects($this->once())
            ->method('adapterFetch')
            ->will($this->returnValue($return));
        
        $res->setUri('http://example.com');
        return $res;
    }
    
}