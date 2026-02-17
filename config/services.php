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
    'fc' => [
        'rate_name' => env('FC_RATE_NAME', 'WWW_SA'),
        'soap_endpoint' => env('FC_SOAP_ENDPOINT', 'http://fcsistemas.ddns.net:8092/wsSAHM2011.asmx'),
        'pass'          => env('FC_SOAP_PASS'),
        'cx'            => env('FC_SOAP_CX'),
        'hold_ttl_minutes' => env('FC_HOLD_TTL_MINUTES', 30),
        'dummy_cc'          => env('FC_DUMMY_CC', '0000000000000000'),
    ],
    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
        'public' => env('STRIPE_PUBLIC_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ]

];
