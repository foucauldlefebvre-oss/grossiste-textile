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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'roly' => [
        'base_url' => env('ROLY_BASE_URL', 'https://api.gorfactory.es'),
        'username' => env('ROLY_EMAIL'),
        'password' => env('ROLY_PASSWORD'),
        'company' => env('ROLY_COMPANY', 'GOR'),
    ],

    'imbretex' => [
        'base_url' => env('IMBRETEX_URL', 'https://api.preprod.imbretex-upgrade.hegyd.net/api'),
        'token' => env('IMBRETEX_TOKEN'),
        'csrf_token' => env('IMBRETEX_CSRF_TOKEN'),
    ],

    'valento' => [
        'base_url' => env('VALENTO_URL', 'https://www.valento.es/rest'),
        'username' => env('VALENTO_USERNAME'),
        'password' => env('VALENTO_PASSWORD'),
    ],

    'solo' => [
        'base_url' => env('SOLO_BASE_URL', 'https://api.sologroup-paris.com'),
        'token_url' => env('SOLO_TOKEN_URL', ''),
        'client_id' => env('SOLO_CLIENT_ID'),
        'client_secret' => env('SOLO_CLIENT_SECRET'),
    ],

    'toptex' => [
        'base_url' => env('TOPTEX_URL', 'https://api.toptex.io'),
        'api_key' => env('TOPTEX_API_KEY'),
        'username' => env('TOPTEX_USERNAME'),
        'password' => env('TOPTEX_PASSWORD'),
        'region' => 'eu-central-1',
        'user_pool_id' => 'eu-central-1_V3BkPppla',
        'client_id' => '14cv2vs23uk99j9hla9iovkm19',
    ],

    // ─── Systempay ────────────────────────────────────────────
    'systempay' => [
        'shop_id' => env('SYSTEMPAY_SHOP_ID', '39994382'),
        'mode' => env('SYSTEMPAY_MODE', 'TEST'), // TEST or PRODUCTION
        'api_url' => env('SYSTEMPAY_API_URL', 'https://api.systempay.fr'),
        'password_test' => env('SYSTEMPAY_PASSWORD_TEST', ''),
        'password_prod' => env('SYSTEMPAY_PASSWORD_PROD', ''),
        'key_test' => env('SYSTEMPAY_KEY_TEST', ''),
        'key_prod' => env('SYSTEMPAY_KEY_PROD', ''),
        'public_key_test' => env('SYSTEMPAY_PUBLIC_KEY_TEST', ''),
        'public_key_prod' => env('SYSTEMPAY_PUBLIC_KEY_PROD', ''),
        'hmac_key_test' => env('SYSTEMPAY_HMAC_KEY_TEST', ''),
        'hmac_key_prod' => env('SYSTEMPAY_HMAC_KEY_PROD', ''),
        'js_url' => 'https://static.systempay.fr/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js',
    ],

];
