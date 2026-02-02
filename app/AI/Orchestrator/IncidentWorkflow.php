<?php

namespace App\AI\Orchestrator;

use App\AI\Config\NeuronConfig;
use App\Models\IncidentRun;
use Illuminate\Support\Facades\Log;

class IncidentWorkflow
{
    public function run(string $text, ?callable $progressCallback = null): array
    {
        $workflowStartTime = microtime(true);
        $inputLength = mb_strlen($text);

        Log::info('Workflow started', [
            'input_length' => $inputLength,
            'input_preview' => mb_substr($text, 0, 200),
            'llm_enabled' => NeuronConfig::shouldUseLLM(),
            'llm_configured' => NeuronConfig::isConfigured(),
        ]);

        $workflow = IncidentNeuronWorkflow::makeForText($text);
        $handler = $workflow->start();

        // If callback provided, iterate streamEvents to call callback
        if ($progressCallback !== null) {
            foreach ($handler->streamEvents() as $event) {
                if (is_array($event) && isset($event['type']) && $event['type'] === 'agent-progress') {
                    $this->notifyProgress(
                        $progressCallback,
                        $event['agent'] ?? '',
                        $event['step'] ?? 0,
                        $event['totalSteps'] ?? 6,
                        $event['status'] ?? 'unknown'
                    );
                }
            }
        }

        $finalState = $handler->getResult();
        $incidentData = $finalState->get('incident');
        $incident = IncidentState::fromArray($incidentData);
        $status = $finalState->get('status', 'processed');

        $workflowTime = (microtime(true) - $workflowStartTime) * 1000;
        $run = $this->persistIfAvailable($text, $incident, $status);

        Log::info('Workflow completed', [
            'status' => $status,
            'total_execution_time_ms' => round($workflowTime, 2),
            'run_id' => $run?->id,
            'linear_issue_id' => $incident->linearIssueId,
            'linear_issue_url' => $incident->linearIssueUrl,
        ]);

        $this->delay();

        return [
            'status' => $status,
            'run_id' => $run?->id,
            'state' => $incident->toArray(),
        ];
    }

    private function persistIfAvailable(string $text, IncidentState $state, string $status): ?IncidentRun
    {
        if (! class_exists(IncidentRun::class)) {
            return null; // Si no has creat el model/migration, no passa res.
        }

        return IncidentRun::create([
            'input_text' => $text,
            'state_json' => $state->toArray(),
            'trace_json' => $state->trace,
            'status' => $status,
            'linear_issue_id' => $state->linearIssueId,
            'linear_issue_url' => $state->linearIssueUrl,
        ]);
    }

    /**
     * Run workflow as a generator that yields events in real-time.
     * This allows for true streaming of progress events.
     *
     * @return \Generator<string, array>
     */
    public function runStreaming(string $text): \Generator
    {
        $workflowStartTime = microtime(true);
        $inputLength = mb_strlen($text);

        Log::info('Workflow started (streaming)', [
            'input_length' => $inputLength,
            'input_preview' => mb_substr($text, 0, 200),
            'llm_enabled' => NeuronConfig::shouldUseLLM(),
            'llm_configured' => NeuronConfig::isConfigured(),
        ]);

        $workflow = IncidentNeuronWorkflow::makeForText($text);
        $handler = $workflow->start();

        $status = null;
        $finalState = null;

        foreach ($handler->streamEvents() as $event) {
            if (is_array($event) && isset($event['type'])) {
                if ($event['type'] === 'agent-progress') {
                    // Map to the expected format
                    yield 'agent-progress' => [
                        'agent' => $event['agent'] ?? '',
                        'step' => $event['step'] ?? 0,
                        'totalSteps' => $event['totalSteps'] ?? 6,
                        'status' => $event['status'] ?? 'unknown',
                        'decision' => $event['decision'] ?? null,
                    ];
                }
            }
        }

        $finalState = $handler->getResult();
        $incidentData = $finalState->get('incident');
        $incident = IncidentState::fromArray($incidentData);
        $status = $finalState->get('status', 'processed');

        $workflowTime = (microtime(true) - $workflowStartTime) * 1000;
        $run = $this->persistIfAvailable($text, $incident, $status);

        Log::info('Workflow completed', [
            'status' => $status,
            'total_execution_time_ms' => round($workflowTime, 2),
            'run_id' => $run?->id,
            'linear_issue_id' => $incident->linearIssueId,
            'linear_issue_url' => $incident->linearIssueUrl,
        ]);

        $this->delay();

        yield 'workflow-result' => [
            'status' => $status,
            'run_id' => $run?->id,
            'state' => $incident->toArray(),
        ];
    }

    private function notifyProgress(?callable $callback, string $agentName, int $step, int $totalSteps, string $status): void
    {
        if ($callback !== null) {
            $callback($agentName, $step, $totalSteps, $status);
        }
    }

    private function delay(): void
    {
        // Avoid artificial delays when running automated tests
        if (app()->runningUnitTests()) {
            return;
        }

        usleep(500000);
    }
}
