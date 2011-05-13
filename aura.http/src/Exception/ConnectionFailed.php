<?php

namespace aura\http;

class Exception_ConnectionFailed extends Exception
{
    public function __construct($error_num, $error_msg)
    {
        $message = "Connection failed: ({$error_num}) {$error_msg}";
        
        parent::__construct($message, $error_num);
    }
}