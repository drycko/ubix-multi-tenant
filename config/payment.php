<?php

return [
    /*|--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    */

    'default_gateway' => env('DEFAULT_PAYMENT_GATEWAY', 'payfast'),

    /*|--------------------------------------------------------------------------
    | Supported Payment Gateways Configuration
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'payfast' => [
            'merchant_id' => env('PAYFAST_MERCHANT_ID', '10000100'),
            'merchant_key' => env('PAYFAST_MERCHANT_KEY', '46f0cd694581a'),
            'passphrase' => env('PAYFAST_PASSPHRASE', ''),
            'url' => env('PAYFAST_URL', 'https://sandbox.payfast.co.za/eng/process'),
        ],
        // Additional gateways can be added here
    ],
];