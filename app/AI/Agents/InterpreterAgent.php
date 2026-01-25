<?php

namespace App\AI\Agents;

use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;
use App\AI\Traits\ChecksBypass;
use Illuminate\Support\Facades\Log;

class InterpreterAgent implements AgentInterface
{
    use ChecksBypass;

    public function name(): string
    {
        return 'Interpreter';
    }

    public function handle(IncidentState $state): IncidentState
    {
        if ($this->shouldBypass()) {
            return $state;
        }
        $startTime = microtime(true);
        $text = trim($state->rawText);
        $inputLength = mb_strlen($text);

        Log::info('Agent execution started', [
            'agent' => $this->name(),
            'method' => 'heuristic',
            'input_length' => $inputLength,
            'input_preview' => mb_substr($text, 0, 200),
        ]);

        $state->summary = mb_substr($text, 0, 160);
        $state->intent = $this->guessIntent($text);
        $state->entities = $this->extractEntities($text);

        $executionTime = (microtime(true) - $startTime) * 1000;

        $output = [
            'summary' => $state->summary,
            'intent' => $state->intent,
            'entities' => $state->entities,
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

    private function guessIntent(string $text): string
    {
        $t = mb_strtolower($text);
        if (str_contains($t, 'error') || str_contains($t, 'falla') || str_contains($t, 'crash')) {
            return 'report_issue';
        }
        if (str_contains($t, 'com ') || str_contains($t, 'cómo') || str_contains($t, 'how')) {
            return 'question';
        }
        if (str_contains($t, 'voldria') || str_contains($t, 'm\'agradaria') || str_contains($t, 'feature')) {
            return 'feature_request';
        }

        return 'unknown';
    }

    private function extractEntities(string $text): array
    {
        $entities = [];
        $keywords = ['kds', 'pos', 'backoffice', 'back-office', 'loyalty', 'linear', 'verifactu', 'kubernetes', 'gcp'];
        $t = mb_strtolower($text);

        foreach ($keywords as $k) {
            if (str_contains($t, $k)) {
                $entities[] = $k;
            }
        }

        return array_values(array_unique($entities));
    }
}
