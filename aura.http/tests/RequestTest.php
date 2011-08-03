<?php

namespace Aura\Http;

use Aura\Http\Request;
use Aura\Http\RequestAdapter;
use Aura\Http\RequestAdapter\MockAdapter as Mock;
use Aura\Http\RequestResponse;
use Aura\Http\ResponseHeaders;
use Aura\Http\ResponseCookies;
use Aura\Http\Uri;
use Aura\Web\Context;

// tmp
require_once '/Volumes/PROJECTS/aura.web/src/Context.php';
require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/src/Uri.php';
require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/src/Request.php';
require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/src/RequestAdapter.php';
require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/src/RequestResponse.php';
require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/src/ResponseHeaders.php';
require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/src/ResponseCookies.php';
require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/tests/MockAdapter.php';

require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/src/Exception.php';
require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/src/Exception/InvalidHandle.php';
require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/src/Exception/UnknownAuthType.php';
require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/src/Exception/UnknownMethod.php';
require_once '/Volumes/PROJECTS/_Github_Nursery/aura.http/src/Exception/UnknownVersion.php';

error_reporting(-1);

function function_exists($func)
{
    $exists = isset($GLOBALS['function_exists']) ? $GLOBALS['function_exists'] : true;
    $GLOBALS['functions_exists'] = true;
    return $exists;
}

class RequestTest extends \PHPUnit_Framework_TestCase
{
//xxx shoud be excep on no uri
    protected function newResource($opts = array())
    {
        $adapter  = new Mock(new RequestResponse(new ResponseHeaders, new ResponseCookies));
        return new Request(new Uri(new Context($GLOBALS), 'http://google.com'), $adapter, $opts);
    }

    public function testSetCookieJar()
    {
        $req = $this->newResource();
        $req->setCookieJar('/a/path/to/file');
        $req->send();

        $this->assertSame('/a/path/to/file', Mock::$options->cookiejar);
    }

    public function testUnsetCookieJar()
    {
        touch(__DIR__ . '/_files/cookietest');

        $req = $this->newResource();
        $req->setCookieJar(__DIR__ . '/_files/cookietest');
        
        // check the file was created for the tests
        $this->assertTrue(file_exists(__DIR__ . '/_files/cookietest'));

        // ready to test unsetting the cookie jar
        $req->setCookieJar(false);

        $this->assertFalse(isset(Mock::$options->cookiejar));
        $this->assertFalse(file_exists(__DIR__ . '/_files/cookietest'));
    }

    public function testSetCookieJarReturnsRequest()
    {
        $req    = $this->newResource();
        $return = $req->setCookieJar('/a/path/to/file');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetHttpAuth()
    {
        $req = $this->newResource();
        
        $req->setHttpAuth('usr', 'pass');
        $req->send();

        $this->assertEquals(array(0 => Request::BASIC, 1 => 'usr:pass', ), 
                          Mock::$options->http_auth);
    }

    public function testUnsetHttpAuth()
    {
        $req = $this->newResource();
        $req->setHttpAuth('usr', 'pass');
        $req->send();

        $this->assertFalse(empty(Mock::$options->http_auth));

        // test unsetting
        $req->setHttpAuth(false, false);
        $req->send();

        $this->assertTrue(empty(Mock::$options->http_auth));
    }

    public function testSetHttpAuthUnknownAuthTypeException()
    {
        $this->setExpectedException('\Aura\Http\Exception\UnknownAuthType');

        $req = $this->newResource();
        $req->setHttpAuth('usr', 'pass', 'FooBar');
    }

    public function testSetHttpAuthColonInHandleException()
    {
        $this->setExpectedException('\Aura\Http\Exception\InvalidHandle');

        $req = $this->newResource();
        $req->setHttpAuth('invalid:handle', 'pass');
    }

    public function testSetHttpAuthReturnsRequest()
    {
        $req    = $this->newResource();
        $return = $req->setHttpAuth('usr', 'pass');
        
        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetUriWithString()
    {
        $req = $this->newResource();
        $req->setUri('http://example.com');
        $req->send();

        $this->assertSame('http://example.com', Mock::$uri);
    }

    public function testSetUriWithUri()
    {
        $uri = new Uri(new Context($GLOBALS), 'http://example.com/path');
        $req = $this->newResource();
        $req->setUri($uri);
        $req->send();

        $this->assertSame('http://example.com/path', Mock::$uri);
    }

    public function testSetUriReturnsRequest()
    {
        $req    = $this->newResource();
        $return = $req->setUri('http://example.com');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetMethod()
    {
        $allowed = array(
            Request::GET,
            Request::POST,
            Request::PUT,
            Request::DELETE,
            Request::TRACE,
            Request::OPTIONS,
            Request::TRACE,
            Request::COPY,
            Request::LOCK,
            Request::MKCOL,
            Request::MOVE,
            Request::PROPFIND,
            Request::PROPPATCH,
            Request::UNLOCK
        );

        foreach ($allowed as $method) {
            $req = $this->newResource();
            $req->setMethod($method)->send();

            $this->assertSame($method, Mock::$method);
        }
    }

    public function testSetMethodUnknownMethodException()
    {
        $this->setExpectedException('\Aura\Http\Exception\UnknownMethod');

        $req = $this->newResource();
        $req->setMethod('INVALID_METHOD');
    }

    public function testSetMethodReturnRequest()
    {
        $req    = $this->newResource();
        $return = $req->setMethod(Request::GET);

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetContentType()
    {
        $req = $this->newResource();
        $req->setContentType('text/text')
            ->setContent('hello')
            ->setMethod(Request::POST)
            ->send();
        
        // charset utf-8 is the default option
        $this->assertSame('text/text; charset=utf-8', Mock::$headers['Content-Type']);
    }

    public function testSetContentTypeAndCharset()
    {
        $req = $this->newResource();
        $req->setContentType('text/text')
            ->setCharset('utf-7')
            ->setContent('hello')
            ->setMethod(Request::POST)
            ->send();
        
        $this->assertSame('text/text; charset=utf-7', Mock::$headers['Content-Type']);
    }

    public function testSetCharsetTypeReturnsRequest()
    {
        $req    = $this->newResource();
        $return = $req->setCharset('utf-8');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetContentTypeReturnsRequest()
    {
        $req    = $this->newResource();
        $return = $req->setContentType('text/text');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetContent()
    {
        $req = $this->newResource();
        $req->setContent('Hello World')
            ->setContentType('text/text')
            ->send();
        
        $this->assertSame('Hello World', Mock::$content);
    }

    public function testSetContentReturnsRequest()
    {
        $req    = $this->newResource();
        $return = $req->setContent('Hello World');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetVersion()
    {
        $req = $this->newResource();
        $req->setVersion('1.0')
            ->send();
        
        $this->assertSame('1.0', Mock::$version);

        $req = $this->newResource();
        $req->setVersion('1.1')
            ->send();
        
        $this->assertSame('1.1', Mock::$version);
    }

    public function testSetVersionUnknownVersionException()
    {
        $this->setExpectedException('\Aura\Http\Exception\UnknownVersion');
        $req = $this->newResource();
        $req->setVersion('100');
    }

    public function testSetVersionReturnsRequest()
    {
        $req    = $this->newResource();
        $return = $req->setVersion('1.1');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetUserAgent()
    {
        $req = $this->newResource();
        $req->setUserAgent('My/UserAgent 1.0')
            ->send();
        
        $this->assertSame('My/UserAgent 1.0', Mock::$headers['User-Agent']);
    }

    public function testSetUserAgentReturnsRequest()
    {
        $req    = $this->newResource();
        $return = $req->setUserAgent('My/UserAgent 1.0');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetEncoding()
    {
        $req = $this->newResource();
        $req->setEncoding()
            ->send();
        
        $this->assertSame('gzip,deflate', Mock::$headers['Accept-Encoding']);
    }

    public function testUnsetEncoding()
    {
        $req = $this->newResource();

        $req->setEncoding()
            ->send();
        
        $this->assertSame('gzip,deflate', Mock::$headers['Accept-Encoding']);


        $req->setEncoding(false)
            ->send();
        
        $this->assertFalse(isset(Mock::$headers['Accept-Encoding']));
    }

    public function testSetEncodingWithoutZlibException()
    {
        $this->setExpectedException('\Aura\Http\Exception');

        $GLOBALS['function_exists'] = false;
        $req = $this->newResource();
        $req->setEncoding();
    }

    public function testSetEncodingReturnsRequest()
    {
        $req    = $this->newResource();
        $return = $req->setEncoding();

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetMaxRedirects()
    {
        $req = $this->newResource();
        $req->setMaxRedirects(42)
            ->send();
        
        $this->assertSame(42, Mock::$options->max_redirects);
    }

    public function testSetMaxRedirectsToDefaultUsingFalse()
    {
        $req = $this->newResource(array('max_redirects' => 11));

        $req->setMaxRedirects(42)
            ->send();
        
        $this->assertSame(42, Mock::$options->max_redirects);

        $req->setMaxRedirects(false)
            ->send();
        
        $this->assertSame(11, Mock::$options->max_redirects);
    }

    public function testSetMaxRedirectsToDefaultUsingNull()
    {
        $req = $this->newResource(array('max_redirects' => 11));

        $req->setMaxRedirects(42)
            ->send();
        
        $this->assertSame(42, Mock::$options->max_redirects);

        $req->setMaxRedirects(null)
            ->send();
        
        $this->assertSame(11, Mock::$options->max_redirects);
    }

    public function testSetMaxRedirectsReturnsRequest()
    {
        $req    = $this->newResource();
        $return = $req->setMaxRedirects(42);

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetTimeout()
    {
        $req = $this->newResource();
        $req->setTimeout(42)
            ->send();
        
        $this->assertSame(42.0, Mock::$options->timeout);
    }

    public function testSetTimeoutToDefaultUsingFalse()
    {
        $req = $this->newResource(array('timeout' => 11));

        $req->setTimeout(42)
            ->send();
        
        $this->assertSame(42.0, Mock::$options->timeout);

        $req->setTimeout(false)
            ->send();
        
        $this->assertSame(11.0, Mock::$options->timeout);
    }

    public function testSetTimeoutToDefaultUsingNull()
    {
        $req = $this->newResource(array('timeout' => 11));

        $req->setTimeout(42)
            ->send();
        
        $this->assertSame(42.0, Mock::$options->timeout);

        $req->setTimeout(null)
            ->send();
        
        $this->assertSame(11.0, Mock::$options->timeout);
    }

    public function testSetTimeoutReturnsRequest()
    {
        $req    = $this->newResource();
        $return = $req->setTimeout(42);

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }


}
