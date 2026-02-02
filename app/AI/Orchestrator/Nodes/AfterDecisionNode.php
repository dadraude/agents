<?php

namespace App\AI\Orchestrator\Nodes;

use App\AI\Orchestrator\Events\DecisionMakerCompletedEvent;
use App\AI\Orchestrator\Events\LinearWriterStartEvent;
use App\AI\Orchestrator\IncidentState;
use Generator;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\StopEvent;
use NeuronAI\Workflow\WorkflowState;

class AfterDecisionNode extends Node
{
    public function __invoke(DecisionMakerCompletedEvent $event, WorkflowState $state): Event|Generator
    {
        $incidentData = $state->get('incident');
        $incident = IncidentState::fromArray($incidentData);

        if (! $incident->shouldEscalate) {
            $status = 'processed';
            $state->set('status', $status);
            $state->set('incident', $incident->toArray());

            yield ['type' => 'agent-progress', 'agent' => 'LinearWriter', 'step' => 6, 'totalSteps' => 6, 'status' => 'skipped'];
            Log::info('Workflow step: Skipping LinearWriter', ['step' => 6, 'total_steps' => 6, 'reason' => 'shouldEscalate=false']);

            return new StopEvent(['status' => $status]);
        }

        // Should escalate - continue to LinearWriter
        return new LinearWriterStartEvent;
    }
}
