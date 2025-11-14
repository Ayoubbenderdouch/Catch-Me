<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging
    |--------------------------------------------------------------------------
    */

    'firebase' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'sender_id' => env('FCM_SENDER_ID'),
        'credentials' => env('FIREBASE_CREDENTIALS'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Maps API
    |--------------------------------------------------------------------------
    */

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

];
