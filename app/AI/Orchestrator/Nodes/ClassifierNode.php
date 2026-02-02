<?php

namespace App\AI\Orchestrator\Nodes;

use App\AI\Agents\ClassifierAgent;
use App\AI\Neuron\ClassifierNeuronAgent;
use App\AI\Orchestrator\Events\ClassifierDoneEvent;
use App\AI\Orchestrator\Events\InterpreterDoneEvent;
use App\AI\Orchestrator\IncidentState;
use Generator;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\WorkflowState;

class ClassifierNode extends Node
{
    public function __invoke(InterpreterDoneEvent $event, WorkflowState $state): Event|Generator
    {
        $incidentData = $state->get('incident');
        $incident = IncidentState::fromArray($incidentData);

        $step = 2;
        $totalSteps = 6;
        $agentName = 'Classifier';

        if (NodeHelper::isBypassed($agentName)) {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'bypassed'];
            Log::info('Workflow step: Classifier bypassed', ['step' => $step, 'total_steps' => $totalSteps]);
        } else {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'processing'];
            Log::info('Workflow step: Classifier', ['step' => $step, 'total_steps' => $totalSteps]);

            $agent = NodeHelper::getAgent(ClassifierAgent::class, ClassifierNeuronAgent::class);
            $incident = $agent->handle($incident);

            $decision = NodeHelper::getAgentDecisionSummary($agentName, $incident);
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'completed', 'decision' => $decision];
        }

        $state->set('incident', $incident->toArray());

        return new ClassifierDoneEvent;
    }
}
