<?php

namespace App\AI\Agents;

use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;

class DecisionMakerAgent implements AgentInterface
{
    public function name(): string
    {
        return 'DecisionMaker';
    }

    public function handle(IncidentState $state): IncidentState
    {
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

        $state->addTrace($this->name(), [
            'shouldEscalate' => $state->shouldEscalate,
            'reason' => $state->decisionReason,
        ]);

        return $state;
    }
}
