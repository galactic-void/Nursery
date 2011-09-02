
## Instantiation

### Dependency Injection

### Manual
    use Aura\Http\Request;
    use Aura\Http\RequestAdapter\Curl;
    use Aura\Http\RequestResponse;
    use Aura\Http\ResponseHeaders;
    use Aura\Http\ResponseCookies;
    use Aura\Http\Uri;
    use Aura\Web\Context;

    $adapter  = new Curl(new RequestResponse(new ResponseHeaders, new ResponseCookies)
    $response = new Request(new Uri(new Context($GLOBALS)), $adapter);

## Making a Request
Making a GET request to Github to list Auras repositories in JSON format:

    $response = $request->setUri('http://github.com/api/v2/json/repos/show/auraphp')
                        ->setMethod(Request::GET) // Not strictly necessary unless you have changed the default options; GET is the default.
                        ->send();

The `$response` is a `\SplStack` containing all the responses including redirects, the stack order is last in first out. Each item in the stack is a `\Aura\Http\RequestResponse` object.

Listing the repositories as an array:

    $repos = json_decode($response[0]->getContent());
    

## Submitting a Request
    
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

## Downloading a File
    
    $response = $request->setUri('http://localhost/download.ext')
                        ->setMethod(Request::GET)
                        ->send();

In the example above the download is stored in memory. For larger files you will probably want to save the download to disk as it is received. This can be done by specifying a full path to a directory or file that is writeable by PHP as an argument in the `send()` method.

    $response = $request->setUri('http://localhost/download.ext')
                        ->setMethod(Request::GET)
                        ->send('/a/path');

When you save a file to disk `RequestResponse->getContent()` returns a file resource.

## Uploading a File

    $response = $request->setUri('http://localhost/submit.php')
                        ->setMethod(Request::POST)
                        ->setContent(array('name' => 'value', 'file' => array('@/a/path/file.ext', @/a/path/file2.ext')))
                        ->send();

## Submitting Custom Content

    $json     = json_encode(array('hello' => 'world'));
    $response = $request->setUri('http://localhost/submit.php')
                        ->setMethod(Request::POST)
                        ->setContent($json)
                        ->setHeader('Content-Type', 'application/json')
                        ->send();

## HTTP Authorization

## OAuth 1.0 Authorization

### The Connection Manager

Setting the connections:

    $manager = new OAuthConnectionManager;

    $manager->set('twitter', new OAuth(...));
    $manager->set('dropbox', new OAuth(...));
    $manager->set('google',  new OAuth(...));

Optionally you can pass an array of `name => oauth` connections to the constructor:

    $connections = array('twitter' => new OAuth(...));
    $manager     = new OAuthConnectionManager($connections);

Fetching a connection:

    $twitter = $manager->get('twitter'); // returns a OAuth library connection configured for Twitter.

    $manager->get('does_not_exist'); // throws an `Exception\UnknownConnection` exception.

### Exceptions

### The Twitter Example
Sending a tweet using OAuth and Request.

    $oauth = new OAuth($request, 
        $response,
        new Storage\Session, 
        new Signature\HmacSha1,
        'http://api.twitter.com/oauth', 
        OAuth::POST,  
        'key', 
        'secret'
    );

    if (!$oauth->hasAccessToken()) {

        $verify = empty($_GET['oauth_verifier']) ? false : $_GET['oauth_verifier'];

        // xxx explain
        $oauth->fetchAuthorization('http://localhost:8888/oauth.php', $verify);
    }

Twitter requires POST for the authentication dance but when accessing the api you are required to use the authentication header (OAuth::HTTP).

    $oauth->setAuthorizationMethod(OAuth::HTTP);

    $result = $request->setOAuth($oauth)
            ->setUri('http://api.twitter.com/1/statuses/update.json')
            ->setMethod(Request::POST)
            ->setContent(array('status' => 'setting up my twitter'))
            ->send();

    echo $result[0]->getContent();

### The Dropbox Example
Dropbox is using OAuth 1.0 so the example is more verbose.

    $oauth = new OAuth($request, 
        $response,
        new Storage\Session, 
        new Signature\HmacSha1,
        'https://api.dropbox.com/0/oauth/', 
        OAuth::HTTP,  
        'key', 
        'secret'
    );

    if (!$oauth->hasAccessToken()) {

        // The base url for Dropbox is different for authorize.
        $oauth->setAuthorizationUri('https://www.dropbox.com/0/oauth/authorize');

        if($oauth->hasRequestToken()) {
            // OAuth 1.0 does not use a verify code.
            $oauth->getAccessToken(null);
        } else {
            $oauth->getRequestToken();
            // getUserAuthorizion will redirect and exit.
            $oauth->getUserAuthorization(array('oauth_callback' => 'http://localhost:8888/oauth.php'));
        }
    }

    // Fetch the users account info
    $result = $request->setOAuth($oauth)
            ->setUri('https://api.dropbox.com/0/account/info')
            ->setMethod(Request::GET)
            ->send();

    echo $result[0]->getContent();

### The Google Example

## Cookie authorization

## Changing the default options

## Reusing a Request object
