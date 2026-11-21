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
        'token' => env('POSTMARK_TOKEN'),
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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', rtrim(env('APP_URL'), '/').'/auth/google/callback'),
    ],

    'whatsapp' => [
        'admin_number' => env('WHATSAPP_ADMIN_NUMBER'),
        'cloud_api' => [
            'access_token' => env('WHATSAPP_CLOUD_API_TOKEN'),
            'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
            'api_version' => env('WHATSAPP_CLOUD_API_VERSION'),
            'order_template_name' => env('WHATSAPP_ORDER_TEMPLATE_NAME'),
            'order_template_language' => env('WHATSAPP_ORDER_TEMPLATE_LANGUAGE', 'id'),
        ],
    ],
];
