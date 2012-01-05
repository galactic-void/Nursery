<?php

/**
 * Constructor params.
 */
$di->params['Aura\Http\Request'] = [
    'adapter' => $di->lazyNew('Aura\Http\RequestAdapter\Curl'),
];
$di->params['Aura\Http\RequestAdapter\Curl'] = [
    'response'     => $di->lazyNew('Aura\Http\RequestResponse'),
];
$di->params['Aura\Http\RequestResponse'] = [
    'headers' => $di->lazyNew('Aura\Http\ResponseHeaders'),
    'cookies' => $di->lazyNew('Aura\Http\ResponseCookies'),
];

/**
 * Dependency services.
 */
$di->set('http_request', function() use ($di) {
    return $di->newInstance('Aura\Http\Request');
});