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

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
        'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
        'version' => env('ANTHROPIC_VERSION', '2023-06-01'),
    ],

    'groq' => [
        // Primary + fallback API keys. The service tries them in order on
        // every request; a 429 from one rotates to the next. Stops trying
        // when one succeeds or all keys are exhausted.
        'api_keys' => array_values(array_filter([
            env('GROQ_API_KEY'),
            env('GROQ_API_KEY_FALLBACK'),
            env('GROQ_API_KEY_2'),
            env('GROQ_API_KEY_3'),
        ])),
        'api_key' => env('GROQ_API_KEY'), // back-compat single-key consumers
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
    ],

    'gemini' => [
        // Primary + fallback API keys. Service tries them in order; a 429
        // (or 401/403 from a revoked key) rotates the sticky pointer to the
        // next key. Stops on success or when all keys are exhausted. Mirrors
        // the Groq pool — Gemini's free tier has 1500 RPD per key, so 3 keys
        // give a soft 4500 RPD ceiling.
        'api_keys' => array_values(array_filter([
            env('GEMINI_API_KEY'),
            env('GEMINI_API_KEY_FALLBACK'),
            env('GEMINI_API_KEY_2'),
            env('GEMINI_API_KEY_3'),
            env('GEMINI_API_KEY_4'),
            env('GEMINI_API_KEY_5'),
            env('GEMINI_API_KEY_6'),
        ])),
        'api_key' => env('GEMINI_API_KEY'), // back-compat single-key consumers
        'model' => env('GEMINI_MODEL', 'gemini-2.5-pro'),
        // Flash is used as a cheaper sub-model for clarifying-question
        // turns and fact extraction — they're short JSON calls that
        // don't need Pro-level reasoning. ~4× cheaper input, ~4× cheaper
        // output. Set LAWYER_CLARIFYING_MODEL=gemini-2.5-flash to opt in.
        'flash_model' => env('GEMINI_FLASH_MODEL', 'gemini-2.5-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    ],

    'voyage' => [
        'api_key' => env('VOYAGE_API_KEY'),
        'model' => env('VOYAGE_MODEL', 'voyage-3'),
        'base_url' => env('VOYAGE_BASE_URL', 'https://api.voyageai.com/v1'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    ],

    'eastlaws' => [
        'base_url' => env('EASTLAWS_BASE_URL', 'https://www.eastlaws.com'),
        'username' => env('EASTLAWS_USERNAME'),
        'password' => env('EASTLAWS_PASSWORD'),
        'enabled' => env('EASTLAWS_ENABLED', false),
        'request_delay_ms' => (int) env('EASTLAWS_REQUEST_DELAY_MS', 3000),
    ],

    // Stripe — used by Laravel Cashier and the BillingController fallbacks.
    // Cashier reads `cashier.key/secret/webhook` from its own config (see
    // `config/cashier.php` once it's published), but `services.stripe.*` is
    // the conventional path other code and our own helpers read.
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => (int) env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
        // Legacy single-key path used by BillingController::stripeConfigured().
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
