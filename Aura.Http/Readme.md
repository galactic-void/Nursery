

Getting Started With Request
============================
 
Instantiation
-------------

```php
<?php
use Aura\Http as Http;
use Aura\Http\Request as Request;
use Aura\Http\Factory\Cookie as CookieFactory;
use Aura\Http\Factory\Header as HeaderFactory;
use Aura\Http\Factory\ResponseStack as StackFactory;

require_once 'src.php';

$headers = new Http\Headers(new HeaderFactory);
$cookies = new Http\Cookies(new CookieFactory);

$response         = new Request\Response($headers, $cookies);
$response_builder = new Request\ResponseBuilder($response, new StackFactory);

$adapter = new Request\Adapter\Curl($response_builder);
$request = new Request($adapter, $headers, $cookies);
```

Available Adapters
------------------

Curl
:    `Aura\Http\Request\Adapter\Curl`

Stream
:    `Aura\Http\Request\Adapter\Stream`   
     Note: Stream is not suitable for uploading large files. When uploading files the entire file(s) is loaded into memory, this is due to a limitation in PHP HTTP Streams.

Making a Request
----------------

Making a GET request to Github to list Auras repositories in JSON format:

    <?php
    $response = $request->get('http://github.com/api/v2/json/repos/show/auraphp');

The `$response` is a `Aura\Http\Request\ResponseStack` containing all the responses including redirects, the stack order is last in first out. Each item in the stack is a `\Aura\Http\RequestResponse` object.

Listing the repositories as an array:

    <?php
    $repos = json_decode($response[0]->getContent());
    

Submitting a Request
--------------------
```php
    <?php    
    $response = $request->setContent(['name' => 'value', 'foo' => ['bar']])
                        ->post('http://localhost/submit.php');
```
 
Downloading a File
------------------    

    <?php
    $response = $request->get('http://localhost/download.ext');

In the example above the download is stored in memory. For larger files you will probably want to save the download to disk as it is received. This is done using the `saveTo()` method and a full path to a file or directory that is writeable by PHP as an argument.

    <?php
    $response = $request->saveTo('/a/path')
                        ->get('http://localhost/download.ext');

When you save a file to disk `$response[0]->getContent()` will return a file resource.

Uploading a File
----------------

    <?php
    $response = $request->setContent(['name' => 'value', 'file' => ['@/a/path/file.ext', '@/a/path/file2.ext']])
                        ->post('http://localhost/submit.php');

Submitting Custom Content
-------------------------

    <?php
    $json     = json_encode(array('hello' => 'world'));
    $response = $request->setContent($json)
                        ->setHeader('Content-Type', 'application/json')
                        ->post('http://localhost/submit.php');

HTTP Authorization
------------------

HTTP Basic:

    <?php
    $response = $request->setHttpAuth('usr', 'pass') // defaults to Request::BASIC
                        ->get('http://localhost/private/index.php');

HTTP Digest:

    $response = $request->setHttpAuth('usr', 'pass', Request::DIGEST)
                        ->get('http://localhost/private/index.php');

Cookies and cookie authorization
--------------------------------

 Note: Currently the CookieJar file that Curl creates is incompatible with the Streams CookieJar file and vis versa.

 Logging into a site using cookies (Although if the site has CSRF protection in place this won't work):

    <?php
    $request->setCookieJar('/path/to/cookiejar')
            ->setContent(['usr_name' => 'name', 'usr_pass' => 'pass'])
            ->post('http://www.example.com/login');


    $response = $request->setCookieJar('/path/to/cookiejar')
                        ->get('http://www.example.com/');
 
