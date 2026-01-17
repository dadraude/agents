<?php

namespace App\AI\Neuron;

use App\AI\Agents\ClassifierAgent;
use App\AI\Orchestrator\IncidentState;

class ClassifierNeuronAgent extends BaseNeuronAgent
{
    protected function getPromptPath(): string
    {
        return app_path('AI/Prompts/classifier.prompt.txt');
    }

    protected function getBackgroundInstructions(): string
    {
        return 'You are an expert at classifying support tickets by type (bug, question, feature, other) and area (pos, kds, backoffice, loyalty, infra, other), and determining if developer intervention is required.';
    }

    protected function getHeuristicAgentClass(): string
    {
        return ClassifierAgent::class;
    }

    public function name(): string
    {
        return 'Classifier';
    }

    protected function applyToState(IncidentState $state, array $parsedData): IncidentState
    {
        $state->type = $parsedData['type'] ?? 'other';
        $state->area = $parsedData['area'] ?? 'other';
        $state->devRelated = $parsedData['devRelated'] ?? false;

        $this->addTrace($state, [
            'type' => $state->type,
            'area' => $state->area,
            'devRelated' => $state->devRelated,
        ]);

        return $state;
    }
}
