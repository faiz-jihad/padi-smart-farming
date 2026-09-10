<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-TEST-KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-TEST-KEY'),
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => true,
    'is_3ds' => true,
];
