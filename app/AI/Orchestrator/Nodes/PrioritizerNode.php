<?php

namespace App\AI\Orchestrator\Nodes;

use App\AI\Agents\PrioritizerAgent;
use App\AI\Neuron\PrioritizerNeuronAgent;
use App\AI\Orchestrator\Events\DecisionMakerDoneEvent;
use App\AI\Orchestrator\Events\PrioritizerDoneEvent;
use App\AI\Orchestrator\IncidentState;
use Generator;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\WorkflowState;

class PrioritizerNode extends Node
{
    public function __invoke(PrioritizerDoneEvent $event, WorkflowState $state): Event|Generator
    {
        $incidentData = $state->get('incident');
        $incident = IncidentState::fromArray($incidentData);

        $step = 4;
        $totalSteps = 6;
        $agentName = 'Prioritizer';

        if (NodeHelper::isBypassed($agentName)) {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'bypassed'];
            Log::info('Workflow step: Prioritizer bypassed', ['step' => $step, 'total_steps' => $totalSteps]);
        } else {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'processing'];
            Log::info('Workflow step: Prioritizer', ['step' => $step, 'total_steps' => $totalSteps]);

            $agent = NodeHelper::getAgent(PrioritizerAgent::class, PrioritizerNeuronAgent::class);
            $incident = $agent->handle($incident);

            $decision = NodeHelper::getAgentDecisionSummary($agentName, $incident);
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'completed', 'decision' => $decision];
        }

        $state->set('incident', $incident->toArray());

        return new DecisionMakerDoneEvent();
    }
}
