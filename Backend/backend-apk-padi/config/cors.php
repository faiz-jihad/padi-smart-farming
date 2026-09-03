<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => env('CORS_ALLOWED_ORIGINS')
        ? array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS'))))
        : ['*'],

    'allowed_origins_patterns' => [
        '#^http://localhost(:[0-9]+)?$#',
        '#^http://127\.0\.0\.1(:[0-9]+)?$#',
        '#^http://10\.0\.2\.2(:[0-9]+)?$#',
        '#^http://192\.168\.[0-9]+\.[0-9]+(:[0-9]+)?$#',
        '#^http://10\.[0-9]+\.[0-9]+\.[0-9]+(:[0-9]+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
