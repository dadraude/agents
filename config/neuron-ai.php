<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used by
    | Neuron AI. Supported providers: "anthropic", "openai", "ollama", "gemini", "mistral"
    |
    */

    'default_provider' => env('NEURON_AI_PROVIDER', 'anthropic'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each AI provider that you
    | wish to use with Neuron AI.
    |
    */

    'providers' => [
        'anthropic' => [
            'key' => env('ANTHROPIC_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
        ],

        'openai' => [
            'key' => env('OPENAI_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
        ],

        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'llama2'),
        ],

        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        ],

        'mistral' => [
            'key' => env('MISTRAL_KEY'),
            'model' => env('MISTRAL_MODEL', 'mistral-small-latest'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Use LLM Flag
    |--------------------------------------------------------------------------
    |
    | This flag controls whether to use LLM-based agents or fallback to
    | heuristic-based agents. Set to true to enable LLM processing.
    |
    */

    'use_llm' => env('AI_USE_LLM', false),

    /*
    |--------------------------------------------------------------------------
    | Timeout Settings
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for LLM responses before timing out.
    |
    */

    'timeout' => env('NEURON_AI_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retry Settings
    |--------------------------------------------------------------------------
    |
    | Number of retries for failed LLM requests.
    |
    */

    'retries' => env('NEURON_AI_RETRIES', 2),

];
