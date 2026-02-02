<?php

namespace App\AI\Orchestrator\Nodes;

use App\AI\Config\NeuronConfig;
use App\AI\Contracts\AgentInterface;
use App\AI\Orchestrator\IncidentState;
use App\Models\AppSetting;

class NodeHelper
{
    public static function getAgent(string $heuristicClass, string $neuronClass): AgentInterface
    {
        if (NeuronConfig::shouldUseLLM() && NeuronConfig::isConfigured()) {
            return app($neuronClass);
        }

        return app($heuristicClass);
    }

    public static function getAgentDecisionSummary(string $agentName, IncidentState $state): ?string
    {
        switch ($agentName) {
            case 'Interpreter':
                if ($state->intent) {
                    return "Intent: {$state->intent}";
                }
                if ($state->summary) {
                    $summary = mb_strlen($state->summary) > 50 ? mb_substr($state->summary, 0, 50).'...' : $state->summary;

                    return $summary;
                }

                return null;
            case 'Classifier':
                $parts = [];
                if ($state->type) {
                    $parts[] = $state->type;
                }
                if ($state->area) {
                    $parts[] = strtoupper($state->area);
                }

                return count($parts) > 0 ? implode(' • ', $parts) : null;
            case 'Validator':
                if ($state->isSufficient === false) {
                    $missing = count($state->missingInfo) > 0
                        ? count($state->missingInfo).' missing'
                        : 'Insufficient';

                    return "Missing info: {$missing}";
                }

                return 'Sufficient info';
            case 'Prioritizer':
                if ($state->priorityScore !== null) {
                    return 'Priority: '.number_format($state->priorityScore, 1);
                }
                if ($state->severity) {
                    return "Severity: {$state->severity}/5";
                }

                return null;
            case 'DecisionMaker':
                if ($state->decisionReason) {
                    $reason = mb_strlen($state->decisionReason) > 60
                        ? mb_substr($state->decisionReason, 0, 60).'...'
                        : $state->decisionReason;

                    return $reason;
                }
                if ($state->shouldEscalate) {
                    return 'Escalate to agents';
                }

                return 'Auto-process';
            case 'LinearWriter':
                if ($state->linearIssueUrl) {
                    return 'Issue created';
                }

                return 'No issue needed';
            default:
                return null;
        }
    }

    public static function isBypassed(string $agentName): bool
    {
        $settings = AppSetting::get();

        return $settings->isBypassed($agentName);
    }
}
