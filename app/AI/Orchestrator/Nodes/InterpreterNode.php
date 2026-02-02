<?php

namespace App\AI\Orchestrator\Nodes;

use App\AI\Agents\InterpreterAgent;
use App\AI\Neuron\InterpreterNeuronAgent;
use App\AI\Orchestrator\Events\InterpreterDoneEvent;
use App\AI\Orchestrator\IncidentState;
use Generator;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\StartEvent;
use NeuronAI\Workflow\WorkflowState;

class InterpreterNode extends Node
{
    public function __invoke(StartEvent $event, WorkflowState $state): Event|Generator
    {
        $incidentData = $state->get('incident');
        $incident = IncidentState::fromArray($incidentData);

        $step = 1;
        $totalSteps = 6;
        $agentName = 'Interpreter';

        if (NodeHelper::isBypassed($agentName)) {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'bypassed'];
            Log::info('Workflow step: Interpreter bypassed', ['step' => $step, 'total_steps' => $totalSteps]);
        } else {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'processing'];
            Log::info('Workflow step: Interpreter', ['step' => $step, 'total_steps' => $totalSteps]);

            $agent = NodeHelper::getAgent(InterpreterAgent::class, InterpreterNeuronAgent::class);
            $incident = $agent->handle($incident);

            $decision = NodeHelper::getAgentDecisionSummary($agentName, $incident);
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'completed', 'decision' => $decision];
        }

        $state->set('incident', $incident->toArray());

        return new InterpreterDoneEvent;
    }
}
