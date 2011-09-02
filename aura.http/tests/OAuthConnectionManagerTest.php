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
$files = glob('{/Users/lee/Dropbox/Development/_Github_Nursery/aura.http/src/*.php,' .
              '/Users/lee/Dropbox/Development/_Github_Nursery/aura.http/src/Exception/*.php,' . 
              '/Users/lee/Dropbox/Development/_Github_Nursery/aura.http/src/RequestAdapter/*.php,' . 
              '/Users/lee/Dropbox/Development/_Github_Nursery/aura.http/src/Signature/*.php,' . 
              '/Users/lee/Dropbox/Development/_Github_Nursery/aura.http/src/Storage/*.php}' 
              , GLOB_BRACE);

foreach ($files as $file) {
  //  echo $file."\n\n";
    require_once $file;
}

require_once '/Users/lee/Dropbox/Development/aura.web/src/Context.php';
require_once '/Users/lee/Dropbox/Development/aura.http/src/Response.php';
require_once '/Users/lee/Dropbox/Development/aura.http/src/Headers.php';
require_once '/Users/lee/Dropbox/Development/aura.http/src/Cookies.php';

error_reporting(-1);


class OAuthConnectionManagerTest extends \PHPUnit_Framework_TestCase
{
    protected function newOAuth()
    {
        $adapter  = new Mock(new RequestResponse(new ResponseHeaders, new ResponseCookies));
        $request  = new Request(new Uri(new Context($GLOBALS)), $adapter);
        $response = new Response(new Headers, new Cookies);

        return new OAuth($request, 
            $response,
            new Storage\Session, 
            new Signature\HmacSha1,
            'http://example.com/oauth', 
            OAuth::POST,  
            'key', 
            'secret'
        );
    }

    public function testSetConnectionsThroughConstructor()
    {
        $test1 = $this->newOAuth();
        $test2 = $this->newOAuth();

        $connections = array(
            'test1' => $test1,
            'test2' => $test2,
        );
        $manager     = new OAuthConnectionManager($connections);

        $this->assertSame($connections, $manager->listAll());
    }

    public function testGet()
    {
        $oauth   = $this->newOAuth();
        $manager = new OAuthConnectionManager(array('test' => $oauth));
        
        $this->assertSame($oauth, $manager->get('test'));
    }

    public function testGetUnknownException()
    {
        $this->setExpectedException('\Aura\Http\Exception\UnknownConnection');

        $oauth   = $this->newOAuth();
        $manager = new OAuthConnectionManager(array('test' => $oauth));
        
        $this->assertSame($oauth, $manager->get('does_not_exist'));
    }

    public function testSet()
    {
        $oauth   = $this->newOAuth();
        $manager = new OAuthConnectionManager();
        $manager->set('test', $oauth);
        
        $this->assertSame($oauth, $manager->get('test'));
    }
}