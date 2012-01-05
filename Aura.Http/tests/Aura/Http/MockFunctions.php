<?php

namespace Aura\Http;

function function_exists($func)
{
    $exists = isset($GLOBALS['function_exists']) ? $GLOBALS['function_exists'] : true;
    $GLOBALS['functions_exists'] = true;
    return $exists;
}