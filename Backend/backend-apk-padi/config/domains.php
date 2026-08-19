<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base Domain
    |--------------------------------------------------------------------------
    |
    | The root domain for the P.A.D.I. application.
    | Farmer public profiles are served under subdomains of this domain.
    |
    | Local development: localhost
    | Production:        padi.id
    |
    */
    'base' => env('APP_BASE_DOMAIN', 'localhost'),
];
