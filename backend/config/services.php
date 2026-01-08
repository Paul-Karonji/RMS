<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, M-Pesa, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'kes'),
    ],

    'mpesa' => [
        'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode' => env('MPESA_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),
        'initiator_name' => env('MPESA_INITIATOR_NAME'),
        'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
        'callback_url' => env('MPESA_CALLBACK_URL'),
        'result_url' => env('MPESA_RESULT_URL'),
        'timeout_url' => env('MPESA_TIMEOUT_URL'),
    ],

    'platform' => [
        'fee_percentage' => env('PLATFORM_FEE_PERCENTAGE', 10.00),
        'cashout_fee_percentage' => env('PLATFORM_CASHOUT_FEE_PERCENTAGE', 3.00),
        'min_cashout_amount' => env('MIN_CASHOUT_AMOUNT', 1000.00),
        'currency' => env('DEFAULT_CURRENCY', 'KES'),
    ],

    'subscription' => [
        'weekly' => env('SUBSCRIPTION_WEEKLY', 500.00),
        'monthly' => env('SUBSCRIPTION_MONTHLY', 1500.00),
        'annual' => env('SUBSCRIPTION_ANNUAL', 15000.00),
    ],

];
