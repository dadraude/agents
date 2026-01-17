<?php

namespace App\AI\Neuron;

use App\AI\Agents\InterpreterAgent;
use App\AI\Orchestrator\IncidentState;

class InterpreterNeuronAgent extends BaseNeuronAgent
{
    protected function getPromptPath(): string
    {
        return app_path('AI/Prompts/interpreter.prompt.txt');
    }

    protected function getBackgroundInstructions(): string
    {
        return 'You are an expert at analyzing support tickets and extracting key information including summary, intent, and relevant entities.';
    }

    protected function getHeuristicAgentClass(): string
    {
        return InterpreterAgent::class;
    }

    public function name(): string
    {
        return 'Interpreter';
    }

    protected function applyToState(IncidentState $state, array $parsedData): IncidentState
    {
        $state->summary = $parsedData['summary'] ?? mb_substr($state->rawText, 0, 160);
        $state->intent = $parsedData['intent'] ?? 'unknown';
        $state->entities = $parsedData['entities'] ?? [];

        $this->addTrace($state, [
            'summary' => $state->summary,
            'intent' => $state->intent,
            'entities' => $state->entities,
        ]);

        return $state;
    }
}
