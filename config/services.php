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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'meta_capi' => [
        'enabled' => env('META_CAPI_ENABLED', false),
        'dataset_id' => env('META_CAPI_DATASET_ID'),
        'access_token' => env('META_CAPI_ACCESS_TOKEN'),
        'api_version' => env('META_CAPI_API_VERSION', 'v26.0'),
        'test_event_code' => env('META_CAPI_TEST_EVENT_CODE'),
        'timeout' => env('META_CAPI_TIMEOUT', 4),
    ],

    'meta_catalog' => [
        'feed_token' => env('META_CATALOG_FEED_TOKEN'),
        'currency' => env('META_CATALOG_CURRENCY', 'INR'),
        'default_brand' => env('META_CATALOG_DEFAULT_BRAND', env('APP_NAME')),
    ],

    // Live Instagram feed for the homepage (Instagram API with Instagram Login).
    // INSTAGRAM_ACCESS_TOKEN = long-lived token of the client's Instagram Business/Creator account.
    // The token is refreshed automatically (stored in storage/app/instagram-token.json).
    'instagram' => [
        'access_token' => env('INSTAGRAM_ACCESS_TOKEN'),
        'limit' => (int) env('INSTAGRAM_FEED_LIMIT', 3),
        'cache_minutes' => (int) env('INSTAGRAM_CACHE_MINUTES', 30),
    ],

];
