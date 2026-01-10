<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OTPIQ Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OTPIQ SMS/WhatsApp verification service
    |
    */

    'base_url' => env('OTPIQ_BASE_URL', 'https://api.otpiq.com/api'),
    'api_key' => env('OTPIQ_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | SMS Provider
    |--------------------------------------------------------------------------
    |
    | Default provider to use for sending SMS
    | Options: 'whatsapp-sms', 'sms'
    |
    */

    'default_provider' => env('OTPIQ_DEFAULT_PROVIDER', 'whatsapp-sms'),

    /*
    |--------------------------------------------------------------------------
    | Verification Code Settings
    |--------------------------------------------------------------------------
    |
    | Settings for verification codes
    |
    */

    'code' => [
        'length' => env('OTPIQ_CODE_LENGTH', 6),
        'expires_in_minutes' => env('OTPIQ_CODE_EXPIRES_IN', 10),
        'max_attempts' => env('OTPIQ_MAX_ATTEMPTS', 5),
    ],
];
