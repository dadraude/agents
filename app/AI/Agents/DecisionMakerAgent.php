<?php

namespace App\AI\Agents;

use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;
use Illuminate\Support\Facades\Log;

class DecisionMakerAgent implements AgentInterface
{
    public function name(): string
    {
        return 'DecisionMaker';
    }

    public function handle(IncidentState $state): IncidentState
    {
        $startTime = microtime(true);
        $inputLength = mb_strlen($state->rawText);

        Log::info('Agent execution started', [
            'agent' => $this->name(),
            'method' => 'heuristic',
            'input_length' => $inputLength,
            'input_preview' => mb_substr($state->rawText, 0, 200),
            'type' => $state->type,
            'priority_score' => $state->priorityScore,
        ]);

        $should = false;
        $reason = 'Not escalated by default.';

        // Regla: escalar si bug/feature i score alt
        if (($state->type === 'bug' || $state->type === 'feature') && ($state->priorityScore ?? 0) >= 4.0) {
            $should = true;
            $reason = 'High priority bug/feature based on impact/urgency/severity.';
        }

        // També: bloqueig operatiu
        $t = mb_strtolower($state->rawText);
        if (str_contains($t, 'blocked') || str_contains($t, 'bloquejat') || str_contains($t, 'no podem cobrar')) {
            $should = true;
            $reason = 'User is blocked (payment/operations).';
        }

        $state->shouldEscalate = $should;
        $state->decisionReason = $reason;

        $executionTime = (microtime(true) - $startTime) * 1000;

        $output = [
            'shouldEscalate' => $state->shouldEscalate,
            'reason' => $state->decisionReason,
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
