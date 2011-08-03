<?php

namespace Aura\Http\RequestAdapter;

class MockAdapter implements \Aura\Http\RequestAdapter
{
    public static $options;
    public static $headers;
    public static $uri;
    public static $method;
    public static $version;
    public static $content;


    public function __construct()
    {
        self::$options = array();
        self::$headers = array();
        self::$uri     = '';
        self::$method  = '';
        self::$content = '';
        self::$version = '';
    }

    public function connect($url, $version)
    {
        self::$uri     = $url;
        self::$version = $version;
    }

    public function setOptions(\ArrayObject $options)
    {
        self::$options = $options;
    }

    public function exec($method, array $headers, $content)
    {
        self::$method  = $method;
        self::$headers = $headers;
        self::$content = $content;
    }
}
