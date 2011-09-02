<?php

namespace Aura\Http\Exception;

class UnableToDecompressContent extends \Aura\Http\Exception
{
    protected $content;
    
    public function __construct($data)
    {
        $this->content = $data;
        $msg = 'Unable to uncompress the response content.';
        parent::__construct($msg);
    }
    
    public function getContent()
    {
        return $this->content;
    }
}