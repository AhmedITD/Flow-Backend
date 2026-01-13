<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pay-as-you-go Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the pay-as-you-go billing system.
    |
    */

    // Minimum top-up amount in IQD
    'min_topup_amount' => env('PAYGO_MIN_TOPUP', 1000),

    // Default currency
    'currency' => env('PAYGO_CURRENCY', 'IQD'),

    // Balance warning threshold (notify user when balance drops below this)
    'low_balance_threshold' => env('PAYGO_LOW_BALANCE', 5000),

    // Auto-suspend account when balance goes negative (for prepaid accounts)
    'auto_suspend' => env('PAYGO_AUTO_SUSPEND', true),

    // Default credit limit for new accounts (0 = prepaid only)
    'default_credit_limit' => env('PAYGO_DEFAULT_CREDIT_LIMIT', 0),

    // Invoice settings
    'invoice' => [
        // Days until invoice is due
        'due_days' => env('PAYGO_INVOICE_DUE_DAYS', 30),
        
        // Auto-generate monthly invoices for postpaid accounts
        'auto_generate' => env('PAYGO_INVOICE_AUTO_GENERATE', true),
    ],

    // Pricing defaults (if no pricing in database)
    'default_pricing' => [
        'call_center' => [
            'price_per_1k_tokens' => 50.00,
            'min_tokens' => 100,
        ],
        'hr' => [
            'price_per_1k_tokens' => 75.00,
            'min_tokens' => 100,
        ],
    ],
];
