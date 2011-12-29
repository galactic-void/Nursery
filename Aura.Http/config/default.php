<?php

/**
 * Constructor params.
 */
$di->params['Aura\Http\Request'] = array(
    'uri'     => $di->lazyNew('Aura\Http\Uri'),
    'adapter' => $di->lazyNew('Aura\Http\RequestAdapter\Curl'),
);
$di->params['Aura\Http\RequestAdapter\Curl'] = array(
    'response'     => $di->lazyNew('Aura\Http\RequestResponse'),
);
$di->params['Aura\Http\RequestResponse'] = array(
    'headers' => $di->lazyNew('Aura\Http\ResponseHeaders'),
    'cookies' => $di->lazyNew('Aura\Http\ResponseCookies'),
);

/**
 * Dependency services.
 */
$di->set('http_request', function() use ($di) {
    return $di->newInstance('Aura\Http\Request');
});
$di->set('http_uri', function() use ($di) {
    return $di->newInstance('Aura\Http\Uri');
});