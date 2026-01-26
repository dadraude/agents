<?php

namespace App\AI\Agents;

use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;
use App\AI\Traits\ChecksBypass;
use App\Integrations\Linear\LinearClient;
use App\Integrations\Linear\LinearMapper;
use Illuminate\Support\Facades\Log;

class LinearWriterAgent implements AgentInterface
{
    use ChecksBypass;

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
        if ($this->shouldBypass()) {
            return $state;
        }
        $startTime = microtime(true);

        Log::info('Agent execution started', [
            'agent' => $this->name(),
            'method' => 'heuristic',
            'linear_configured' => $this->client->isConfigured(),
            'should_escalate' => $state->shouldEscalate,
        ]);

        // Generate payload always (for dry run display)
        $payload = $this->mapper->mapStateToIssuePayload($state);

        // Dry run si no tens API key configurada
        if (! $this->client->isConfigured()) {
            $output = [
                'dryRun' => true,
                'message' => 'LINEAR_API_KEY missing. Skipping ticket creation.',
                'payload' => $payload,
            ];

            $state->addTrace($this->name(), $output);

            $executionTime = (microtime(true) - $startTime) * 1000;

            Log::info('Agent execution completed (dry run)', [
                'agent' => $this->name(),
                'method' => 'heuristic',
                'execution_time_ms' => round($executionTime, 2),
                'output' => $output,
            ]);

            return $state;
        }

        Log::info('Creating Linear issue', [
            'agent' => $this->name(),
            'payload' => $payload,
        ]);

        $issue = $this->client->createIssue($payload);

        $state->linearIssueId = $issue['id'] ?? null;
        $state->linearIssueUrl = $issue['url'] ?? null;

        $executionTime = (microtime(true) - $startTime) * 1000;

        $output = [
            'created' => (bool) $state->linearIssueId,
            'issue' => [
                'id' => $state->linearIssueId,
                'url' => $state->linearIssueUrl,
                'identifier' => $issue['identifier'] ?? null,
            ],
            'error' => $issue['error'] ?? false,
            'payload' => $payload,
        ];

        $state->addTrace($this->name(), $output);

        Log::info('Agent execution completed', [
            'agent' => $this->name(),
            'method' => 'heuristic',
            'execution_time_ms' => round($executionTime, 2),
            'output' => $output,
        ]);

        return $state;
    }
}
