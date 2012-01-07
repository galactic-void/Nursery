
## Instantiation
 todo
 
### Dependency Injection
 todo
 
### Manual
    use Aura\Http\Request;
    use Aura\Http\RequestAdapter\Curl;
    use Aura\Http\RequestResponse;
    use Aura\Http\ResponseHeaders;
    use Aura\Http\ResponseCookies;

    $adapter  = new Curl(new RequestResponse(new ResponseHeaders, new ResponseCookies)
    $response = new Request($adapter);

## Making a Request
Making a GET request to Github to list Auras repositories in JSON format:

    $response = $request->setUrl('http://github.com/api/v2/json/repos/show/auraphp')
                        ->setMethod(Request::GET) // Not necessary unless you have changed the default options; GET is the default.
                        ->send();

The `$response` is a `\SplStack` containing all the responses including redirects, the stack order is last in first out. Each item in the stack is a `\Aura\Http\RequestResponse` object.

Listing the repositories as an array:

    $repos = json_decode($response[0]->getContent());
    

## Submitting a Request
    
    $response = $request->setUrl('http://localhost/submit.php')
                        ->setMethod(Request::POST)
                        ->setContent(['name' => 'value', 'foo' => ['bar']])
                        ->send();

## Exceptions
Exceptions thrown by Request:
Exceptions thrown by RequestResponse:

 
## Downloading a File
    
    $response = $request->setUrl('http://localhost/download.ext')
                        ->setMethod(Request::GET)
                        ->send();

In the example above the download is stored in memory. For larger files you will probably want to save the download to disk as it is received. This can be done by specifying a full path to a directory or file that is writeable by PHP as an argument in the `send()` method.

    $response = $request->setUrl('http://localhost/download.ext')
                        ->setMethod(Request::GET)
                        ->send('/a/path');

When you save a file to disk `$response[0]->getContent()` will return a file resource.

## Uploading a File

    $response = $request->setUrl('http://localhost/submit.php')
                        ->setMethod(Request::POST)
                        ->setContent(['name' => 'value', 'file' => ['@/a/path/file.ext', '@/a/path/file2.ext']])
                        ->send();

## Submitting Custom Content

    $json     = json_encode(array('hello' => 'world'));
    $response = $request->setUrl('http://localhost/submit.php')
                        ->setMethod(Request::POST)
                        ->setContent($json)
                        ->setHeader('Content-Type', 'application/json')
                        ->send();

## HTTP Authorization
HTTP Basic:

    $response = $request->setUrl('http://localhost/private/index.php')
                        ->setHttpAuth('usr', 'pass') // defaults to Request::BASIC
                        ->send();

HTTP Digest:

    $response = $request->setUrl('http://localhost/private/index.php')
                        ->setHttpAuth('usr', 'pass', Request::DIGEST)
                        ->send();

## Cookies and cookie authorization
 todo
 
## Changing the default options
 todo
 
