<?php

namespace aura\http;

class Exception_UnableToUncompressContent extends Exception
{
    protected $content;
    
    public function __construct($data)
    {
        $this->content = $data;
        $msg = 'Unable to uncompress response content.';
        parent::__construct($msg);
    }
    
    public function getContent()
    {
        return $this->content;
    }
}