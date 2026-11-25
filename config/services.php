<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'tax_api' => [
        'url' => env('TAX_REFERENCE_API_URL', 'https://pajakin.test/api'),
        'key' => env('TAX_REFERENCE_API_KEY'),
        'timeout' => (int) env('TAX_REFERENCE_API_TIMEOUT', 3),
    ],

    'region_api' => [
        'url' => env('REGION_API_URL', 'https://emsifa.github.io/api-wilayah-indonesia/api'),
        'timeout' => (int) env('REGION_API_TIMEOUT', 3),
    ],

    'midtrans' => [
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
        'server_key' => (string) env('MIDTRANS_SERVER_KEY', ''),
        'client_key' => (string) env('MIDTRANS_CLIENT_KEY', ''),
        'timeout' => (int) env('MIDTRANS_TIMEOUT', 10),
    ],

];
