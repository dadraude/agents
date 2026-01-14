<?php

namespace App\AI\Agents;

use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;
use App\Integrations\Linear\LinearClient;
use App\Integrations\Linear\LinearMapper;

class LinearWriterAgent implements AgentInterface
{
    public function __construct(
        private readonly LinearClient $client,
        private readonly LinearMapper $mapper,
    ) {}

    public function name(): string
    {
        return 'LinearWriter';
    }

    public function handle(IncidentState $state): IncidentState
    {
        // Dry run si no tens API key configurada
        if (! $this->client->isConfigured()) {
            $state->addTrace($this->name(), [
                'dryRun' => true,
                'message' => 'LINEAR_API_KEY missing. Skipping ticket creation.',
            ]);

            return $state;
        }

        $payload = $this->mapper->mapStateToIssuePayload($state);
        $issue = $this->client->createIssue($payload);

        $state->linearIssueId = $issue['id'] ?? null;
        $state->linearIssueUrl = $issue['url'] ?? null;

        $state->addTrace($this->name(), [
            'created' => (bool) $state->linearIssueId,
            'issue' => [
                'id' => $state->linearIssueId,
                'url' => $state->linearIssueUrl,
                'identifier' => $issue['identifier'] ?? null,
            ],
            'error' => $issue['error'] ?? false,
        ]);

        return $state;
    }
}
