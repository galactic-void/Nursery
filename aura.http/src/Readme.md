
## Instantiation

    use Aura\Http\Request;
    use Aura\Http\RequestAdapter\Curl;
    use Aura\Http\RequestResponse;
    use Aura\Http\ResponseHeaders;
    use Aura\Http\ResponseCookies;
    use Aura\Http\Uri;
    use Aura\Web\Context;

    $adapter  = new Curl(new RequestResponse(new ResponseHeaders, new ResponseCookies)
    $response = new Request(new Uri(new Context($GLOBALS)), $adapter);

## Making a request
Making a GET request to Github to list Auras repositories in JSON format:

    $response = $request->setUri('http://github.com/api/v2/json/repos/show/auraphp')
                        ->setMethod(Request::GET) // Not strictly necessary unless you have changed the default options; GET is the default.
                        ->send();

The `$response` is a `\SplStack` containing all the responses including redirects, the stack order is last in first out. Each item in the stack is a `\Aura\Http\RequestResponse` object.

Listing the repositories as an array:

    $repos = json_decode($response[0]->getContent());
    

## Submitting a request
    
    $response = $request->setUri('http://localhost/submit.php')
                        ->setMethod(Request::POST)
                        ->setContent(array('name' => 'value', 'foo' => array('bar')))
                        ->send();

The value of `$_POST` at `http://localhost/submit.php` will look like this:

    array(
        'name' => 'value',
        'foo'  => array(0 => 'bar')
    )

## Exceptions

## Downloading a file
    
    $response = $request->setUri('http://localhost/download.ext')
                        ->setMethod(Request::GET)
                        ->send();

In the example above the whole download is stored in memory. For larger files you will probably want to save the download to disk as it is received. This can be done by specifying a full path that is writeable by PHP as an argument in the `send()` method.

    $response = $request->setUri('http://localhost/download.ext')
                        ->setMethod(Request::GET)
                        ->send('/a/path');

When you save a download to a file `RequestResponse->getContent()` returns a file resource.

## Uploading a file

    $response = $request->setUri('http://localhost/submit.php')
                        ->setMethod(Request::POST)
                        ->setContent(array('name' => 'value', 'file' => array('@/a/path/file.ext', @/a/path/file2.ext')))
                        ->send();

## Submitting custom content

    $json     = jason_encode(array('hello' => 'world'));
    $response = $request->setUri('http://localhost/submit.php')
                        ->setMethod(Request::POST)
                        ->setContent($json)
                        ->setHeader('Content-Type', 'application/json')
                        ->send();

## Encoding
## HTTP authorization
## Cookie authorization
## Changing the default options