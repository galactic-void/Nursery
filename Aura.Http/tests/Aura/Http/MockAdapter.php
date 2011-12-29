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

    public function connect($url)
    {
        self::$uri     = $url;
    }

    public function setOptions(\ArrayObject $options)
    {
        self::$options = $options;
    }

    public function exec($method, $version, array $headers, $content)
    {
        self::$method  = $method;
        self::$version = $version;
        self::$headers = $headers;
        self::$content = $content;
    }
}
