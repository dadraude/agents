<?php

namespace App\AI\Neuron;

use App\AI\Agents\PrioritizerAgent;
use App\AI\Orchestrator\IncidentState;

class PrioritizerNeuronAgent extends BaseNeuronAgent
{
    protected function getPromptPath(): string
    {
        return app_path('AI/Prompts/prioritizer.prompt.txt');
    }

    protected function getBackgroundInstructions(): string
    {
        return 'You are an expert at prioritizing support tickets based on business impact, urgency, and severity, calculating weighted priority scores.';
    }

    protected function getHeuristicAgentClass(): string
    {
        return PrioritizerAgent::class;
    }

    public function name(): string
    {
        return 'Prioritizer';
    }

    protected function buildUserMessage(IncidentState $state): \NeuronAI\Chat\Messages\UserMessage
    {
        $message = "Ticket type: {$state->type}\n";
        $message .= "Ticket area: {$state->area}\n";
        $message .= "Ticket text: {$state->rawText}";

        return new \NeuronAI\Chat\Messages\UserMessage($message);
    }

    protected function applyToState(IncidentState $state, array $parsedData): IncidentState
    {
        $state->impact = isset($parsedData['impact']) ? (int) $parsedData['impact'] : 3;
        $state->urgency = isset($parsedData['urgency']) ? (int) $parsedData['urgency'] : 3;
        $state->severity = isset($parsedData['severity']) ? (int) $parsedData['severity'] : 3;
        $state->priorityScore = isset($parsedData['priorityScore']) ? (float) $parsedData['priorityScore'] : 3.0;

        $this->addTrace($state, [
            'impact' => $state->impact,
            'urgency' => $state->urgency,
            'severity' => $state->severity,
            'priorityScore' => $state->priorityScore,
        ]);

        return $state;
    }
}
