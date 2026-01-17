<?php

namespace App\AI\Orchestrator;

use App\AI\Agents\ClassifierAgent;
use App\AI\Agents\DecisionMakerAgent;
use App\AI\Agents\InterpreterAgent;
use App\AI\Agents\LinearWriterAgent;
use App\AI\Agents\PrioritizerAgent;
use App\AI\Agents\ValidatorAgent;
use App\AI\Config\NeuronConfig;
use App\AI\Neuron\ClassifierNeuronAgent;
use App\AI\Neuron\DecisionMakerNeuronAgent;
use App\AI\Neuron\InterpreterNeuronAgent;
use App\AI\Neuron\LinearWriterNeuronAgent;
use App\AI\Neuron\PrioritizerNeuronAgent;
use App\AI\Neuron\ValidatorNeuronAgent;
use App\Models\IncidentRun;

class IncidentWorkflow
{
    public function run(string $text): array
    {
        $state = new IncidentState($text);

        // 1) Interpreter
        $state = $this->getAgent(InterpreterAgent::class, InterpreterNeuronAgent::class)->handle($state);

        // 2) Classifier
        $state = $this->getAgent(ClassifierAgent::class, ClassifierNeuronAgent::class)->handle($state);

        // 3) Validator
        $state = $this->getAgent(ValidatorAgent::class, ValidatorNeuronAgent::class)->handle($state);

        if (! $state->isSufficient) {
            $status = 'needs_more_info';
            $this->persistIfAvailable($text, $state, $status);

            return [
                'status' => $status,
                'state' => $state->toArray(),
            ];
        }

        // 4) Prioritizer
        $state = $this->getAgent(PrioritizerAgent::class, PrioritizerNeuronAgent::class)->handle($state);

        // 5) Decision maker
        $state = $this->getAgent(DecisionMakerAgent::class, DecisionMakerNeuronAgent::class)->handle($state);

        // 6) Linear writer (si procedeix)
        if ($state->shouldEscalate) {
            $state = $this->getAgent(LinearWriterAgent::class, LinearWriterNeuronAgent::class)->handle($state);
            $status = 'escalated';
        } else {
            $status = 'processed';
        }

        $run = $this->persistIfAvailable($text, $state, $status);

        return [
            'status' => $status,
            'run_id' => $run?->id,
            'state' => $state->toArray(),
        ];
    }

    private function getAgent(string $heuristicClass, string $neuronClass): \App\AI\Contracts\AgentInterface
    {
        if (NeuronConfig::shouldUseLLM() && NeuronConfig::isConfigured()) {
            return app($neuronClass);
        }

        return app($heuristicClass);
    }

    private function persistIfAvailable(string $text, IncidentState $state, string $status): ?IncidentRun
    {
        if (! class_exists(IncidentRun::class)) {
            return null; // Si no has creat el model/migration, no passa res.
        }

        return IncidentRun::create([
            'input_text' => $text,
            'state_json' => $state->toArray(),
            'trace_json' => $state->trace,
            'status' => $status,
            'linear_issue_id' => $state->linearIssueId,
            'linear_issue_url' => $state->linearIssueUrl,
        ]);
    }
}
