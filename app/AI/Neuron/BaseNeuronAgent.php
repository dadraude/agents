<?php

namespace App\AI\Neuron;

use App\AI\Config\NeuronConfig;
use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;
use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\SystemPrompt;

abstract class BaseNeuronAgent extends Agent implements AgentInterface
{
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
            default => throw new \RuntimeException("Unsupported provider: {$providerName}"),
        };
    }

    protected function instructions(): string
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
        // If LLM is not enabled or not configured, fallback to heuristic agent
        if (! NeuronConfig::shouldUseLLM() || ! NeuronConfig::isConfigured()) {
            return $this->fallbackToHeuristic($state);
        }

        try {
            return $this->processWithLLM($state);
        } catch (\Exception $e) {
            Log::warning("LLM processing failed for {$this->name()}, falling back to heuristic", [
                'error' => $e->getMessage(),
                'ticket_text' => substr($state->rawText, 0, 100),
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

        while ($attempt <= $retries) {
            try {
                $userMessage = $this->buildUserMessage($state);

                // Set timeout if supported by the provider
                $response = $this->chat($userMessage)->run();

                $content = $response->getMessage()->getContent();
                $parsedData = $this->parseJsonResponse($content);

                return $this->applyToState($state, $parsedData);
            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt <= $retries) {
                    Log::warning("LLM attempt {$attempt} failed for {$this->name()}, retrying...", [
                        'error' => $e->getMessage(),
                    ]);
                    // Small delay before retry
                    usleep(500000); // 0.5 seconds
                }
            }
        }

        // If all retries failed, throw the last exception
        throw $lastException ?? new \RuntimeException('LLM processing failed after retries');
    }

    protected function buildUserMessage(IncidentState $state): UserMessage
    {
        return new UserMessage($state->rawText);
    }

    protected function parseJsonResponse(string $content): array
    {
        // Remove markdown code blocks if present
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
                'content' => substr($content, 0, 500),
                'error' => json_last_error_msg(),
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
            throw new \RuntimeException("Heuristic agent class not found: {$heuristicClass}");
        }

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
