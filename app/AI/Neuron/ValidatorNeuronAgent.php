<?php

namespace App\AI\Neuron;

use App\AI\Agents\ValidatorAgent;
use App\AI\Orchestrator\IncidentState;

class ValidatorNeuronAgent extends BaseNeuronAgent
{
    protected function getPromptPath(): string
    {
        return app_path('AI/Prompts/validator.prompt.txt');
    }

    protected function getBackgroundInstructions(): string
    {
        return 'You are an expert at validating support tickets to ensure they contain sufficient information for resolution, especially for bug reports that need steps to reproduce and environment details.';
    }

    protected function getHeuristicAgentClass(): string
    {
        return ValidatorAgent::class;
    }

    public function name(): string
    {
        return 'Validator';
    }

    protected function buildUserMessage(IncidentState $state): \NeuronAI\Chat\Messages\UserMessage
    {
        $message = "Ticket type: {$state->type}\n";
        $message .= "Ticket text: {$state->rawText}";

        return new \NeuronAI\Chat\Messages\UserMessage($message);
    }

    protected function applyToState(IncidentState $state, array $parsedData): IncidentState
    {
        $state->isSufficient = $parsedData['isSufficient'] ?? true;
        $state->missingInfo = $parsedData['missingInfo'] ?? [];

        $this->addTrace($state, [
            'isSufficient' => $state->isSufficient,
            'missingInfo' => $state->missingInfo,
        ]);

        return $state;
    }
}
