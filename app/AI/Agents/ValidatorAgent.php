<?php

namespace App\AI\Agents;

use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;
use Illuminate\Support\Facades\Log;

class ValidatorAgent implements AgentInterface
{
    public function name(): string
    {
        return 'Validator';
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
        ]);

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

        $executionTime = (microtime(true) - $startTime) * 1000;

        $output = [
            'isSufficient' => $state->isSufficient,
            'missingInfo' => $state->missingInfo,
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
