<?php

namespace App\AI\Orchestrator\Nodes;

use App\AI\Agents\LinearWriterAgent;
use App\AI\Config\NeuronConfig;
use App\AI\Neuron\LinearWriterNeuronAgent;
use App\AI\Orchestrator\Events\LinearWriterStartEvent;
use App\AI\Orchestrator\IncidentState;
use Generator;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\StopEvent;
use NeuronAI\Workflow\WorkflowState;

class LinearWriterNode extends Node
{
    public function __invoke(LinearWriterStartEvent $event, WorkflowState $state): Event|Generator
    {
        $incidentData = $state->get('incident');
        $incident = IncidentState::fromArray($incidentData);

        $step = 6;
        $totalSteps = 6;
        $agentName = 'LinearWriter';

        if (NodeHelper::isBypassed($agentName)) {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'bypassed'];
            Log::info('Workflow step: LinearWriter bypassed', ['step' => $step, 'total_steps' => $totalSteps, 'reason' => 'shouldEscalate=true but agent bypassed']);
            $status = 'escalated';
        } else {
            yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'processing'];
            Log::info('Workflow step: LinearWriter', ['step' => $step, 'total_steps' => $totalSteps, 'reason' => 'shouldEscalate=true']);

            $agent = NodeHelper::getAgent(LinearWriterAgent::class, LinearWriterNeuronAgent::class);
            $incident = $agent->handle($incident);

            // Check if Linear issue was actually created
            if (! empty($incident->linearIssueUrl)) {
                $decision = NodeHelper::getAgentDecisionSummary($agentName, $incident);
                yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'completed', 'decision' => $decision];
                $status = 'escalated';
                Log::info('Linear issue created successfully', [
                    'linear_issue_id' => $incident->linearIssueId,
                    'linear_issue_url' => $incident->linearIssueUrl,
                ]);
            } else {
                // Linear issue creation failed - still escalate but log warning
                $decision = 'Failed to create Linear issue';
                yield ['type' => 'agent-progress', 'agent' => $agentName, 'step' => $step, 'totalSteps' => $totalSteps, 'status' => 'completed', 'decision' => $decision];
                $status = 'escalated';
                Log::warning('Linear issue creation failed but ticket still escalated', [
                    'should_escalate' => $incident->shouldEscalate,
                    'linear_configured' => NeuronConfig::isConfigured(),
                ]);
                // Add error message to state for UI display
                $incident->decisionReason = ($incident->decisionReason ?? 'Ticket escalated to agents.').' Warning: Linear issue could not be created.';
            }
        }

        $state->set('incident', $incident->toArray());
        $state->set('status', $status);

        return new StopEvent(['status' => $status]);
    }
}
