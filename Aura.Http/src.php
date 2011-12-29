<?php

require_once __DIR__ . '/src/Aura/Http/Exception/InvalidHandle.php';
require_once __DIR__ . '/src/Aura/Http/Exception/UnableToDecompressContent.php';
require_once __DIR__ . '/src/Aura/Http/Exception/UnknownAuthType.php';
require_once __DIR__ . '/src/Aura/Http/Exception/UnknownMethod.php';
require_once __DIR__ . '/src/Aura/Http/Exception/UnknowStatus.php';
require_once __DIR__ . '/src/Aura/Http/Exception/UnknownVersion.php';

require_once __DIR__ . '/src/Aura/Http/RequestAdapter/Curl.php';
require_once __DIR__ . '/src/Aura/Http/Exception.php';
require_once __DIR__ . '/src/Aura/Http/Request.php';
require_once __DIR__ . '/src/Aura/Http/RequestAdapter.php';
require_once __DIR__ . '/src/Aura/Http/RequestResponse.php';
require_once __DIR__ . '/src/Aura/Http/ResponseCookies.php';
require_once __DIR__ . '/src/Aura/Http/ResponseHeaders.php';
require_once __DIR__ . '/src/Aura/Http/Uri.php';