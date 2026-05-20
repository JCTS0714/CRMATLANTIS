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

    'kapso' => [
        'enabled' => env('KAPSO_ENABLED', false),
        'api_key' => env('KAPSO_API_KEY'),
        'phone_number_id' => env('KAPSO_PHONE_NUMBER_ID'),
        'base_url' => env('KAPSO_BASE_URL', 'https://api.kapso.ai/meta/whatsapp/v24.0'),
        'template_opening' => env('KAPSO_TEMPLATE_OPENING', 'hello_world'),
    ],

    'brevo' => [
        'enabled' => env('BREVO_ENABLED', false),
        'api_key' => env('BREVO_API_KEY'),
        'sender_email' => env('BREVO_SENDER_EMAIL'),
        'sender_name' => env('BREVO_SENDER_NAME', 'CRM Atlantis'),
    ],

];
