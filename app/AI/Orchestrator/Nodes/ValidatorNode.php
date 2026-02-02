<?php

namespace App\AI\Orchestrator\Nodes;

use App\AI\Agents\ValidatorAgent;
use App\AI\Neuron\ValidatorNeuronAgent;
use App\AI\Orchestrator\Events\ClassifierDoneEvent;
use App\AI\Orchestrator\Events\ValidatorDoneEvent;
use App\AI\Orchestrator\IncidentState;
use Generator;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\WorkflowState;

class ValidatorNode extends Node
{
    public function __invoke(ClassifierDoneEvent $event, WorkflowState $state): Event|Generator
    {
        $incidentData = $state->get('incident');
        $incident = IncidentState::fromArray($incidentData);

        $step = 3;
        $totalSteps = 6;
        $agentName = 'Validator';

        if (NodeHelper::isBypassed($agentName)) {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'bypassed'];
            Log::info('Workflow step: Validator bypassed', ['step' => $step, 'total_steps' => $totalSteps]);
        } else {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'processing'];
            Log::info('Workflow step: Validator', ['step' => $step, 'total_steps' => $totalSteps]);

            $agent = NodeHelper::getAgent(ValidatorAgent::class, ValidatorNeuronAgent::class);
            $incident = $agent->handle($incident);

            $decision = NodeHelper::getAgentDecisionSummary($agentName, $incident);
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'completed', 'decision' => $decision];
        }

        $state->set('incident', $incident->toArray());

        return new ValidatorDoneEvent;
    }
}
