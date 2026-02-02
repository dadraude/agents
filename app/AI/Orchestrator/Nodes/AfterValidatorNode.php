<?php

namespace App\AI\Orchestrator\Nodes;

use App\AI\Orchestrator\Events\PrioritizerDoneEvent;
use App\AI\Orchestrator\Events\ValidatorDoneEvent;
use App\AI\Orchestrator\IncidentState;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\StopEvent;
use NeuronAI\Workflow\WorkflowState;

class AfterValidatorNode extends Node
{
    public function __invoke(ValidatorDoneEvent $event, WorkflowState $state): PrioritizerDoneEvent|StopEvent
    {
        $incidentData = $state->get('incident');
        $incident = IncidentState::fromArray($incidentData);

        if (! $incident->isSufficient) {
            $status = 'needs_more_info';
            $state->set('status', $status);
            $state->set('incident', $incident->toArray());

            Log::info('Workflow completed (needs more info)', [
                'status' => $status,
                'missing_info' => $incident->missingInfo,
            ]);

            return new StopEvent(['status' => $status]);
        }

        return new PrioritizerDoneEvent;
    }
}
