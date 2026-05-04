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
        'rate_name' => env('FC_RATE_NAME', 'WWW_CA'),
        'soap_endpoint' => env('FC_SOAP_ENDPOINT', 'http://fcsistemas.ddns.net:8092/wsSAHM2011.asmx'),
        'pass'          => env('FC_SOAP_PASS'),
        'cx'            => env('FC_SOAP_CX'),
        'soap_debug'    => env('FC_SOAP_DEBUG', false),
        'hold_ttl_minutes' => env('FC_HOLD_TTL_MINUTES', 30),
        'dummy_cc'          => env('FC_DUMMY_CC', '0000000000000000'),
    ],
    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
        'public' => env('STRIPE_PUBLIC_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'enzo' => [
        'api_key' => env('ENZO_API_KEY', 'enzo_master_key_zB3JnXRw2Z}ci}~.+,N>'),
        'metrics_url' => env('ENZO_METRICS_URL', 'https://control.enzomarketing.mx/wp-json/enzo-api/v1/update-client-metrics'),
        'post_ids' => [
            'torreon' => (int) env('ENZO_TORREON_POST_ID', 1339),
            'gomez' => (int) env('ENZO_GOMEZ_POST_ID', 6405),
        ],
    ],

    'hotels' => [
        'torreon' => [
            'name' => env('HOTEL_TORREON_NAME', 'Nuve Torreon'),
            'fc' => [
                'rate_name' => env('FC_TORREON_RATE_NAME', env('FC_RATE_NAME', 'WWW_CA')),
                'soap_endpoint' => env('FC_TORREON_SOAP_ENDPOINT', env('FC_SOAP_ENDPOINT', 'http://fcsistemas.ddns.net:8092/wsSAHM2011.asmx')),
                'pass' => env('FC_TORREON_SOAP_PASS', env('FC_SOAP_PASS')),
                'cx' => env('FC_SOAP_CX', env('FC_TORREON_SOAP_CX')),
                'soap_debug' => env('FC_TORREON_SOAP_DEBUG', env('FC_SOAP_DEBUG', false)),
                'hold_ttl_minutes' => env('FC_TORREON_HOLD_TTL_MINUTES', env('FC_HOLD_TTL_MINUTES', 30)),
                'dummy_cc' => env('FC_TORREON_DUMMY_CC', env('FC_DUMMY_CC', '0000000000000000')),
            ],
            'stripe' => [
                'secret' => env('STRIPE_TORREON_SECRET_KEY', env('STRIPE_SECRET_KEY')),
                'public' => env('STRIPE_TORREON_PUBLIC_KEY', env('STRIPE_PUBLIC_KEY')),
                'webhook_secret' => env('STRIPE_TORREON_WEBHOOK_SECRET', env('STRIPE_WEBHOOK_SECRET')),
            ],
        ],
        'gomez' => [
            'name' => env('HOTEL_GOMEZ_NAME', 'Nuve Gomez'),
            'fc' => [
                'rate_name' => env('FC_GOMEZ_RATE_NAME', env('FC_RATE_NAME', 'WWW_CA')),
                'soap_endpoint' => env('FC_GOMEZ_SOAP_ENDPOINT', env('FC_SOAP_ENDPOINT', 'http://fcsistemas.ddns.net:8092/wsSAHM2011.asmx')),
                'pass' => env('FC_GOMEZ_SOAP_PASS', env('FC_SOAP_PASS')),
                'cx' => env('FC_SOAP_CX_GOMEZ', env('FC_GOMEZ_SOAP_CX', env('FC_SOAP_CX'))),
                'soap_debug' => env('FC_GOMEZ_SOAP_DEBUG', env('FC_SOAP_DEBUG', false)),
                'hold_ttl_minutes' => env('FC_GOMEZ_HOLD_TTL_MINUTES', env('FC_HOLD_TTL_MINUTES', 30)),
                'dummy_cc' => env('FC_GOMEZ_DUMMY_CC', env('FC_DUMMY_CC', '0000000000000000')),
            ],
            'stripe' => [
                'secret' => env('STRIPE_GOMEZ_SECRET_KEY', env('STRIPE_SECRET_KEY')),
                'public' => env('STRIPE_GOMEZ_PUBLIC_KEY', env('STRIPE_PUBLIC_KEY')),
                'webhook_secret' => env('STRIPE_GOMEZ_WEBHOOK_SECRET', env('STRIPE_WEBHOOK_SECRET')),
            ],
        ],
    ],

];
