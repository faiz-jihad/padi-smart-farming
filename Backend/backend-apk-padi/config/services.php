<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'weather' => [
        'provider' => env('WEATHER_PROVIDER', 'openweathermap'),
        'api_key' => env('WEATHER_API_KEY'),
        'base_url' => env('WEATHER_BASE_URL', 'https://api.openweathermap.org/data/2.5'),
        'agromonitoring_base_url' => env('AGROMONITORING_BASE_URL', 'https://api.agromonitoring.com/1.0'),
        'timeout' => env('WEATHER_TIMEOUT', 10),
    ],

    'ai' => [
        'base_url' => env('AI_SERVICE_URL', 'http://127.0.0.1:8003/api/v1'),
        'timeout' => env('AI_SERVICE_TIMEOUT', 30),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

];
