<?php
namespace Aura\Http\Signature;



class HmacSha1SignatureTest extends \PHPUnit_Framework_TestCase
{
    public function testHmacSha1Signature()
    {
    	$storage   = $this->getMock('\Aura\Http\OAuthStorage', array(
	    	'isRequestToken', 'isAccessToken',  'setNamespace', 'setTokenType',
	    	'setToken',       'setTokenSecret', 'delete',       'getToken', 
            'getTokenSecret'));

        $storage->expects($this->once())
                ->method('getTokenSecret')
                ->will($this->returnValue('TokenSecret'));

        $signature = new HmacSha1();
        $signature->setStorage($storage)
    	          ->setConsumerSecret('secret')
        	      ->setAuthorizationMethod('HTTP');

        $params    = array('key' => 'value', 'key2' => 'value2');
        $generated = $signature->generateSignature('http://example.com', 'POST', $params);

        $this->assertSame('x6lPm7BV+Ave7KHVs01a7zGqYys=', $generated);
    }
}