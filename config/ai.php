<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Service Configuration
    |--------------------------------------------------------------------------
    |
    | Core settings for the AI-Generated UI Builder.
    |
    */

    'primary_model' => env('AI_MODEL', 'gpt-5.2'),
    
    'openai' => [
        'base_url' => env('OPENAI_API_BASE', 'https://api.openai.com/v1'),
        'temperature' => (float) env('AI_TEMPERATURE', 0.7),
        'max_tokens' => (int) env('AI_MAX_TOKENS', 4000),
        'fallback_model' => env('AI_FALLBACK_MODEL', 'gpt-5.2'),
        'model_aliases' => [
            'gpt-5.4-turbo' => 'gpt-5.4',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Budget & Cost Management
    |--------------------------------------------------------------------------
    |
    | Limits for AI API usage to prevent overspending.
    |
    */

    'monthly_budget_usd' => (float) env('AI_MONTHLY_BUDGET', 50.00),
    
    'warn_cost_threshold' => (float) env('AI_WARN_COST_THRESHOLD', 0.8), // 80%

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting (Per User)
    |--------------------------------------------------------------------------
    |
    | Throttling settings to prevent abuse.
    |
    */

    'rate_limits' => [
        'per_minute' => (int) env('AI_RATE_LIMIT_PER_MINUTE', 10),
        'per_hour' => (int) env('AI_RATE_LIMIT_PER_HOUR', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Generation Settings
    |--------------------------------------------------------------------------
    |
    | Logic constraints for generation sessions.
    |
    */

    'max_refinement_iterations' => 5,
    
    'design_compliance_threshold' => 90, // Percentage
];
