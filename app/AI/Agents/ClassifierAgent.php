<?php

namespace App\AI\Agents;

use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;
use App\AI\Traits\ChecksBypass;
use Illuminate\Support\Facades\Log;

class ClassifierAgent implements AgentInterface
{
    use ChecksBypass;

    public function name(): string
    {
        return 'Classifier';
    }

    public function handle(IncidentState $state): IncidentState
    {
        if ($this->shouldBypass()) {
            return $state;
        }
        $startTime = microtime(true);
        $t = mb_strtolower($state->rawText);
        $inputLength = mb_strlen($state->rawText);

        Log::info('Agent execution started', [
            'agent' => $this->name(),
            'method' => 'heuristic',
            'input_length' => $inputLength,
            'input_preview' => mb_substr($state->rawText, 0, 200),
        ]);

        // type
        if (str_contains($t, 'error') || str_contains($t, 'falla') || str_contains($t, 'crash')) {
            $state->type = 'bug';
        } elseif (str_contains($t, 'com ') || str_contains($t, 'cómo') || str_contains($t, 'how')) {
            $state->type = 'question';
        } elseif (str_contains($t, 'm\'agradaria') || str_contains($t, 'voldria') || str_contains($t, 'feature')) {
            $state->type = 'feature';
        } else {
            $state->type = 'other';
        }

        // area
        $state->area = 'other';
        if (str_contains($t, 'kds')) {
            $state->area = 'kds';
        }
        if (str_contains($t, 'pos')) {
            $state->area = 'pos';
        }
        if (str_contains($t, 'backoffice') || str_contains($t, 'back-office')) {
            $state->area = 'backoffice';
        }
        if (str_contains($t, 'loyalty') || str_contains($t, 'fidel')) {
            $state->area = 'loyalty';
        }
        if (str_contains($t, 'kubernetes') || str_contains($t, 'gcp') || str_contains($t, 'load balancer')) {
            $state->area = 'infra';
        }

        // devRelated
        $state->devRelated = in_array($state->type, ['bug', 'feature'], true);

        $executionTime = (microtime(true) - $startTime) * 1000;

        $output = [
            'type' => $state->type,
            'area' => $state->area,
            'devRelated' => $state->devRelated,
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
