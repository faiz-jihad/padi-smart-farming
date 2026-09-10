<?php

return [
    'plan_name' => 'Government Data Access',
    'plan_days' => (int) env('GOVERNMENT_SUBSCRIPTION_DAYS', 30),
    'plan_price' => (float) env('GOVERNMENT_SUBSCRIPTION_PRICE', 20000),
    'currency' => 'IDR',
    'whatsapp_number' => env('B2G_WHATSAPP_NUMBER', '6281234567890'),
    'base_api_url' => env('APP_URL', 'http://127.0.0.1:8000') . '/api/v1/government',
];
