<?php

namespace App\AI\Neuron;

use App\AI\Config\NeuronConfig;
use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;
use App\AI\Traits\ChecksBypass;
use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\Mistral\Mistral;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\SystemPrompt;

abstract class BaseNeuronAgent extends Agent implements AgentInterface
{
    use ChecksBypass;

    protected function provider(): AIProviderInterface
    {
        $providerName = NeuronConfig::getDefaultProvider();
        $key = NeuronConfig::getProviderKey($providerName);
        $model = NeuronConfig::getProviderModel($providerName);

        return match ($providerName) {
            'anthropic' => new Anthropic(
                key: $key ?? '',
                model: $model ?? 'claude-3-5-sonnet-20241022',
            ),
            'openai' => new OpenAI(
                key: $key ?? '',
                model: $model ?? 'gpt-4',
            ),
            'gemini' => new Gemini(
                key: $key ?? '',
                model: $model ?? 'gemini-2.5-flash',
            ),
            'mistral' => new Mistral(
                key: $key ?? '',
                model: $model ?? 'mistral-small-latest',
            ),
            default => throw new \RuntimeException("Unsupported provider: {$providerName}"),
        };
    }

    public function instructions(): string
    {
        $promptPath = $this->getPromptPath();

        if (! file_exists($promptPath)) {
            throw new \RuntimeException("Prompt file not found: {$promptPath}");
        }

        $promptContent = file_get_contents($promptPath);

        return (string) new SystemPrompt(
            background: [
                $this->getBackgroundInstructions(),
            ],
            steps: [
                'Analyze the provided support ticket text.',
                'Extract the required information according to the specifications.',
                'Return a valid JSON response with the exact structure specified.',
            ],
            output: [
                'You must return ONLY valid JSON, no additional text or markdown.',
                'The JSON must match the exact structure specified in the prompt.',
                $promptContent,
            ]
        );
    }

    abstract protected function getPromptPath(): string;

    abstract protected function getBackgroundInstructions(): string;

    abstract protected function getHeuristicAgentClass(): string;

    public function handle(IncidentState $state): IncidentState
    {
        if ($this->shouldBypass()) {
            return $state;
        }

        // Check if this agent should use LLM (agent-specific or global setting)
        $shouldUseLLM = NeuronConfig::shouldUseLLMForAgent($this->name());
        $globalLLMEnabled = NeuronConfig::shouldUseLLM();

        // If LLM is not enabled for this agent or not configured, fallback to heuristic agent
        if (! $shouldUseLLM || ! NeuronConfig::isConfigured()) {
            Log::info('Agent falling back to heuristic (LLM not enabled or not configured)', [
                'agent' => $this->name(),
                'agent_llm_enabled' => $shouldUseLLM,
                'global_llm_enabled' => $globalLLMEnabled,
                'llm_configured' => NeuronConfig::isConfigured(),
            ]);

            return $this->fallbackToHeuristic($state);
        }

        // Log that we're using LLM (more visible)
        $providerName = NeuronConfig::getDefaultProvider();
        $model = NeuronConfig::getProviderModel($providerName);
        $settings = \App\Models\AppSetting::get();
        $agentSetting = $settings->shouldUseLLMForAgent($this->name());
        $usingAgentSpecificConfig = $agentSetting !== null;

        Log::info('🤖 Using LLM for agent', [
            'agent' => $this->name(),
            'provider' => $providerName,
            'model' => $model,
            'method' => 'llm',
            'config_source' => $usingAgentSpecificConfig ? 'agent_specific' : 'global',
        ]);

        try {
            return $this->processWithLLM($state);
        } catch (\Exception $e) {
            Log::warning("LLM processing failed for {$this->name()}, falling back to heuristic", [
                'agent' => $this->name(),
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'ticket_text_preview' => mb_substr($state->rawText, 0, 200),
            ]);

            return $this->fallbackToHeuristic($state);
        }
    }

    protected function processWithLLM(IncidentState $state): IncidentState
    {
        $timeout = NeuronConfig::getTimeout();
        $retries = NeuronConfig::getRetries();
        $attempt = 0;
        $lastException = null;

        $providerName = NeuronConfig::getDefaultProvider();
        $model = NeuronConfig::getProviderModel($providerName);
        $promptPath = $this->getPromptPath();
        $promptSize = file_exists($promptPath) ? filesize($promptPath) : 0;
        $inputText = $state->rawText;
        $inputLength = mb_strlen($inputText);

        Log::info('Agent execution started', [
            'agent' => $this->name(),
            'method' => 'llm',
            'provider' => $providerName,
            'model' => $model,
            'input_length' => $inputLength,
            'input_preview' => mb_substr($inputText, 0, 200),
            'prompt_path' => $promptPath,
            'prompt_size' => $promptSize,
            'timeout' => $timeout,
            'max_retries' => $retries,
        ]);

        while ($attempt <= $retries) {
            $startTime = microtime(true);
            $attempt++;

            try {
                $userMessage = $this->buildUserMessage($state);

                Log::info('LLM call initiated', [
                    'agent' => $this->name(),
                    'attempt' => $attempt,
                    'input_length' => $inputLength,
                ]);

                // Set timeout if supported by the provider
                $response = $this->chat($userMessage);

                $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
                $content = $response->getContent();
                $contentLength = mb_strlen($content);

                Log::info('LLM call completed', [
                    'agent' => $this->name(),
                    'attempt' => $attempt,
                    'execution_time_ms' => round($executionTime, 2),
                    'response_length' => $contentLength,
                    'response_content' => $content,
                ]);

                $parsedData = $this->parseJsonResponse($content);

                Log::info('LLM response parsed', [
                    'agent' => $this->name(),
                    'parsed_data' => $parsedData,
                ]);

                $result = $this->applyToState($state, $parsedData);

                Log::info('Agent execution completed', [
                    'agent' => $this->name(),
                    'method' => 'llm',
                    'total_execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ]);

                return $result;
            } catch (\Exception $e) {
                $executionTime = (microtime(true) - $startTime) * 1000;
                $lastException = $e;

                Log::error('LLM call failed', [
                    'agent' => $this->name(),
                    'attempt' => $attempt,
                    'execution_time_ms' => round($executionTime, 2),
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);

                if ($attempt <= $retries) {
                    Log::warning("LLM attempt {$attempt} failed for {$this->name()}, retrying...", [
                        'error' => $e->getMessage(),
                        'next_attempt' => $attempt + 1,
                    ]);
                    // Small delay before retry
                    usleep(500000); // 0.5 seconds
                }
            }
        }

        // If all retries failed, throw the last exception
        Log::error('LLM processing failed after all retries', [
            'agent' => $this->name(),
            'total_attempts' => $attempt,
            'last_error' => $lastException?->getMessage(),
        ]);

        throw $lastException ?? new \RuntimeException('LLM processing failed after retries');
    }

    protected function buildUserMessage(IncidentState $state): UserMessage
    {
        return new UserMessage($state->rawText);
    }

    protected function parseJsonResponse(string $content): array
    {
        // Remove markdown code blocks if present
        $originalContent = $content;
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);

        // Try to extract JSON if it's embedded in text
        if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/', $content, $matches)) {
            $content = $matches[0];
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse JSON from LLM response', [
                'agent' => $this->name(),
                'original_content_length' => mb_strlen($originalContent),
                'processed_content' => mb_substr($content, 0, 1000),
                'error' => json_last_error_msg(),
                'json_error_code' => json_last_error(),
            ]);
            throw new \RuntimeException('Invalid JSON response from LLM: '.json_last_error_msg());
        }

        return $decoded;
    }

    abstract protected function applyToState(IncidentState $state, array $parsedData): IncidentState;

    protected function fallbackToHeuristic(IncidentState $state): IncidentState
    {
        $heuristicClass = $this->getHeuristicAgentClass();

        if (! class_exists($heuristicClass)) {
            Log::error('Heuristic agent class not found', [
                'agent' => $this->name(),
                'heuristic_class' => $heuristicClass,
            ]);
            throw new \RuntimeException("Heuristic agent class not found: {$heuristicClass}");
        }

        Log::info('Executing heuristic agent as fallback', [
            'neuron_agent' => $this->name(),
            'heuristic_class' => $heuristicClass,
        ]);

        $heuristicAgent = app($heuristicClass);

        return $heuristicAgent->handle($state);
    }

    protected function addTrace(IncidentState $state, array $data, bool $fromLLM = true): void
    {
        $state->addTrace($this->name(), array_merge($data, [
            'method' => $fromLLM ? 'llm' : 'heuristic',
        ]));
    }
}
