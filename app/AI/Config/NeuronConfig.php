<?php

namespace App\AI\Config;

class NeuronConfig
{
    public static function shouldUseLLM(): bool
    {
        return config('neuron-ai.use_llm', false);
    }

    public static function getDefaultProvider(): string
    {
        return config('neuron-ai.default_provider', 'anthropic');
    }

    public static function getProviderConfig(string $provider): ?array
    {
        return config("neuron-ai.providers.{$provider}");
    }

    public static function getProviderKey(string $provider): ?string
    {
        $config = self::getProviderConfig($provider);

        return $config['key'] ?? null;
    }

    public static function getProviderModel(string $provider): ?string
    {
        $config = self::getProviderConfig($provider);

        return $config['model'] ?? null;
    }

    public static function getTimeout(): int
    {
        return config('neuron-ai.timeout', 30);
    }

    public static function getRetries(): int
    {
        return config('neuron-ai.retries', 2);
    }

    public static function isConfigured(): bool
    {
        $provider = self::getDefaultProvider();
        $config = self::getProviderConfig($provider);

        if (! $config) {
            return false;
        }

        // Check if provider has required key
        if (in_array($provider, ['anthropic', 'openai', 'gemini', 'mistral'], true)) {
            return ! empty($config['key']);
        }

        // For Ollama, just check if base_url is set
        if ($provider === 'ollama') {
            return ! empty($config['base_url']);
        }

        return false;
    }
}
