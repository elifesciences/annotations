<?php

return [
    'api_url' => 'http://api_dummy:8080',
    'hypothesis' => [
        'api_url' => 'http://hypothesis_dummy:8080/',
    ],
    'aws' => [
        'queue_name' => 'annotations--dev',
        'queue_message_default_type' => 'profile',
        'credential_file' => true,
        'region' => 'us-east-1',
        'endpoint' => 'http://goaws:4100',
    ],
];
