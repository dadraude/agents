<?php

namespace App\AI\Agents;

use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;

class ValidatorAgent implements AgentInterface
{
    public function name(): string
    {
        return 'Validator';
    }

    public function handle(IncidentState $state): IncidentState
    {
        $missing = [];

        // Si és bug, demanar passos i entorn (heurístic)
        if ($state->type === 'bug') {
            if (! preg_match('/pas|passos|step|repro/i', $state->rawText)) {
                $missing[] = 'steps_to_reproduce';
            }
            if (! preg_match('/ios|android|version|versi[oó]n|v\d+/i', $state->rawText)) {
                $missing[] = 'environment_version';
            }
        }

        $state->missingInfo = $missing;
        $state->isSufficient = count($missing) === 0;

        $state->addTrace($this->name(), [
            'isSufficient' => $state->isSufficient,
            'missingInfo' => $state->missingInfo,
        ]);

        return $state;
    }
}
