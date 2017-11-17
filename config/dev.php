<?php

use Psr\Log\LogLevel;

return [
    'debug' => true,
    'logging' => [
        'level' => LogLevel::DEBUG,
    ],
    'hypothesis' => [
        'api_url' => 'https://hypothes.is/api',
    ],
    'aws' => [
        'queue_name' => 'annotations--dev',
        'queue_message_default_type' => 'profile',
        'credential_file' => true,
        'region' => 'us-east-1',
        'endpoint' => 'http://localhost:4100',
    ],
];
