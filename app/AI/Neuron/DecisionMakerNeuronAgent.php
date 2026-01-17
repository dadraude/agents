<?php

namespace App\AI\Neuron;

use App\AI\Agents\DecisionMakerAgent;
use App\AI\Orchestrator\IncidentState;

class DecisionMakerNeuronAgent extends BaseNeuronAgent
{
    protected function getPromptPath(): string
    {
        return app_path('AI/Prompts/decision_maker.prompt.txt');
    }

    protected function getBackgroundInstructions(): string
    {
        return 'You are an expert at deciding whether support tickets should be escalated to the development team based on classification, priority, and operational impact.';
    }

    protected function getHeuristicAgentClass(): string
    {
        return DecisionMakerAgent::class;
    }

    public function name(): string
    {
        return 'DecisionMaker';
    }

    protected function buildUserMessage(IncidentState $state): \NeuronAI\Chat\Messages\UserMessage
    {
        $message = "Ticket type: {$state->type}\n";
        $message .= "Ticket area: {$state->area}\n";
        $message .= "Priority score: {$state->priorityScore}\n";
        $message .= "Impact: {$state->impact}, Urgency: {$state->urgency}, Severity: {$state->severity}\n";
        $message .= "Ticket text: {$state->rawText}";

        return new \NeuronAI\Chat\Messages\UserMessage($message);
    }

    protected function applyToState(IncidentState $state, array $parsedData): IncidentState
    {
        $state->shouldEscalate = $parsedData['shouldEscalate'] ?? false;
        $state->decisionReason = $parsedData['decisionReason'] ?? 'Not escalated by default.';

        $this->addTrace($state, [
            'shouldEscalate' => $state->shouldEscalate,
            'reason' => $state->decisionReason,
        ]);

        return $state;
    }
}
