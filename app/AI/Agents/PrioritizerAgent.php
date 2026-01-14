<?php

namespace App\AI\Agents;

use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;

class PrioritizerAgent implements AgentInterface
{
    public function name(): string
    {
        return 'Prioritizer';
    }

    public function handle(IncidentState $state): IncidentState
    {
        // Defaults
        $impact = 3;
        $urgency = 3;
        $severity = 3;
        $t = mb_strtolower($state->rawText);

        // Señals de bloqueig operatiu
        if (str_contains($t, 'no podem cobrar') || str_contains($t, 'no se puede cobrar') || str_contains($t, 'blocked')) {
            $impact = 5;
            $urgency = 5;
            $severity = 5;
        } elseif (str_contains($t, 'intermitent') || str_contains($t, 'sometimes') || str_contains($t, 'de vegades')) {
            $impact = 3;
            $urgency = 3;
            $severity = 3;
        }

        if ($state->type === 'question') {
            $severity = 2;
            $urgency = 2;
        }

        $state->impact = $impact;
        $state->urgency = $urgency;
        $state->severity = $severity;

        $state->priorityScore = round(($impact * 0.4) + ($urgency * 0.35) + ($severity * 0.25), 2);

        $state->addTrace($this->name(), [
            'impact' => $state->impact,
            'urgency' => $state->urgency,
            'severity' => $state->severity,
            'priorityScore' => $state->priorityScore,
        ]);

        return $state;
    }
}
