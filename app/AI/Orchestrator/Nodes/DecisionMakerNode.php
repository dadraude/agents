<?php

namespace App\AI\Orchestrator\Nodes;

use App\AI\Agents\DecisionMakerAgent;
use App\AI\Neuron\DecisionMakerNeuronAgent;
use App\AI\Orchestrator\Events\DecisionMakerDoneEvent;
use App\AI\Orchestrator\IncidentState;
use Generator;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\WorkflowState;

class DecisionMakerNode extends Node
{
    public function __invoke(DecisionMakerDoneEvent $event, WorkflowState $state): Event|Generator
    {
        $incidentData = $state->get('incident');
        $incident = IncidentState::fromArray($incidentData);

        $step = 5;
        $totalSteps = 6;
        $agentName = 'DecisionMaker';

        if (NodeHelper::isBypassed($agentName)) {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'bypassed'];
            Log::info('Workflow step: DecisionMaker bypassed', ['step' => $step, 'total_steps' => $totalSteps]);
        } else {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'processing'];
            Log::info('Workflow step: DecisionMaker', ['step' => $step, 'total_steps' => $totalSteps]);

            $agent = NodeHelper::getAgent(DecisionMakerAgent::class, DecisionMakerNeuronAgent::class);
            $incident = $agent->handle($incident);

            $decision = NodeHelper::getAgentDecisionSummary($agentName, $incident);
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'completed', 'decision' => $decision];
        }

        $state->set('incident', $incident->toArray());

        // Return DecisionMakerCompletedEvent - AfterDecisionNode will route to LinearWriter or StopEvent
        return new \App\AI\Orchestrator\Events\DecisionMakerCompletedEvent();
    }
}
