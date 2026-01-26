<?php

namespace App\AI\Neuron;

use App\AI\Agents\LinearWriterAgent;
use App\AI\Orchestrator\IncidentState;
use App\Integrations\Linear\LinearClient;
use App\Integrations\Linear\LinearMapper;
use NeuronAI\Chat\Messages\UserMessage;

class LinearWriterNeuronAgent extends BaseNeuronAgent
{
    public function __construct(
        private readonly LinearClient $client,
        private readonly LinearMapper $mapper,
    ) {
        // Parent Agent class doesn't require constructor call
    }

    protected function getPromptPath(): string
    {
        return app_path('AI/Prompts/linear_writer.prompt.txt');
    }

    protected function getBackgroundInstructions(): string
    {
        return 'You are an expert at writing clear, technical issue descriptions for development teams in Linear.';
    }

    protected function getHeuristicAgentClass(): string
    {
        return LinearWriterAgent::class;
    }

    public function name(): string
    {
        return 'LinearWriter';
    }

    protected function buildUserMessage(IncidentState $state): UserMessage
    {
        $message = "Generate a technical description for this support ticket:\n\n";
        $message .= "Type: {$state->type}\n";
        $message .= "Area: {$state->area}\n";
        $message .= "Summary: {$state->summary}\n";
        $message .= "Intent: {$state->intent}\n";
        $message .= "Priority Score: {$state->priorityScore}\n";
        $message .= "Impact: {$state->impact}, Urgency: {$state->urgency}, Severity: {$state->severity}\n";
        $message .= 'Entities: '.implode(', ', $state->entities)."\n";
        $message .= "Original text: {$state->rawText}";

        return new UserMessage($message);
    }

    protected function processWithLLM(IncidentState $state): IncidentState
    {
        // Generate base payload first
        $payload = $this->mapper->mapStateToIssuePayload($state);

        // Dry run si no tens API key configurada
        if (! $this->client->isConfigured()) {
            $this->addTrace($state, [
                'dryRun' => true,
                'message' => 'LINEAR_API_KEY missing. Skipping ticket creation.',
                'payload' => $payload,
            ], false);

            return $state;
        }

        // Generate improved description using LLM
        $userMessage = $this->buildUserMessage($state);
        $response = $this->chat($userMessage);
        $description = trim($response->getContent());

        // Override description with LLM-generated one
        if (! empty($description)) {
            $payload['description'] = $description;
        }

        $issue = $this->client->createIssue($payload);

        $state->linearIssueId = $issue['id'] ?? null;
        $state->linearIssueUrl = $issue['url'] ?? null;

        $this->addTrace($state, [
            'created' => (bool) $state->linearIssueId,
            'issue' => [
                'id' => $state->linearIssueId,
                'url' => $state->linearIssueUrl,
                'identifier' => $issue['identifier'] ?? null,
            ],
            'error' => $issue['error'] ?? false,
            'llm_description_used' => ! empty($description),
            'payload' => $payload,
        ]);

        return $state;
    }

    protected function applyToState(IncidentState $state, array $parsedData): IncidentState
    {
        // This method is not used for LinearWriter as it doesn't return JSON
        // The processWithLLM method handles everything
        return $state;
    }
}
