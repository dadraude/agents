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
use App\Models\AppSetting;
use App\Models\IncidentRun;
use Illuminate\Support\Facades\Log;

class IncidentWorkflow
{
    public function run(string $text, ?callable $progressCallback = null): array
    {
        $workflowStartTime = microtime(true);
        $inputLength = mb_strlen($text);

        Log::info('Workflow started', [
            'input_length' => $inputLength,
            'input_preview' => mb_substr($text, 0, 200),
            'llm_enabled' => NeuronConfig::shouldUseLLM(),
            'llm_configured' => NeuronConfig::isConfigured(),
        ]);

        $state = new IncidentState($text);
        $settings = AppSetting::get();

        // 1) Interpreter
        if ($settings->isBypassed('Interpreter')) {
            $this->notifyProgress($progressCallback, 'Interpreter', 1, 6, 'bypassed');
            Log::info('Workflow step: Interpreter bypassed', ['step' => 1, 'total_steps' => 6]);
        } else {
            $this->notifyProgress($progressCallback, 'Interpreter', 1, 6, 'processing');
            Log::info('Workflow step: Interpreter', ['step' => 1, 'total_steps' => 6]);
            $state = $this->getAgent(InterpreterAgent::class, InterpreterNeuronAgent::class)->handle($state);
            $this->notifyProgress($progressCallback, 'Interpreter', 1, 6, 'completed');
        }

        // 2) Classifier
        if ($settings->isBypassed('Classifier')) {
            $this->notifyProgress($progressCallback, 'Classifier', 2, 6, 'bypassed');
            Log::info('Workflow step: Classifier bypassed', ['step' => 2, 'total_steps' => 6]);
        } else {
            $this->notifyProgress($progressCallback, 'Classifier', 2, 6, 'processing');
            Log::info('Workflow step: Classifier', ['step' => 2, 'total_steps' => 6]);
            $state = $this->getAgent(ClassifierAgent::class, ClassifierNeuronAgent::class)->handle($state);
            $this->notifyProgress($progressCallback, 'Classifier', 2, 6, 'completed');
        }

        // 3) Validator
        if ($settings->isBypassed('Validator')) {
            $this->notifyProgress($progressCallback, 'Validator', 3, 6, 'bypassed');
            Log::info('Workflow step: Validator bypassed', ['step' => 3, 'total_steps' => 6]);
        } else {
            $this->notifyProgress($progressCallback, 'Validator', 3, 6, 'processing');
            Log::info('Workflow step: Validator', ['step' => 3, 'total_steps' => 6]);
            $state = $this->getAgent(ValidatorAgent::class, ValidatorNeuronAgent::class)->handle($state);
            $this->notifyProgress($progressCallback, 'Validator', 3, 6, 'completed');
        }

        if (! $state->isSufficient) {
            $status = 'needs_more_info';
            $workflowTime = (microtime(true) - $workflowStartTime) * 1000;
            $this->persistIfAvailable($text, $state, $status);

            Log::info('Workflow completed (needs more info)', [
                'status' => $status,
                'total_execution_time_ms' => round($workflowTime, 2),
                'missing_info' => $state->missingInfo,
            ]);

            $this->delay();

            return [
                'status' => $status,
                'state' => $state->toArray(),
            ];
        }

        // 4) Prioritizer
        if ($settings->isBypassed('Prioritizer')) {
            $this->notifyProgress($progressCallback, 'Prioritizer', 4, 6, 'bypassed');
            Log::info('Workflow step: Prioritizer bypassed', ['step' => 4, 'total_steps' => 6]);
        } else {
            $this->notifyProgress($progressCallback, 'Prioritizer', 4, 6, 'processing');
            Log::info('Workflow step: Prioritizer', ['step' => 4, 'total_steps' => 6]);
            $state = $this->getAgent(PrioritizerAgent::class, PrioritizerNeuronAgent::class)->handle($state);
            $this->notifyProgress($progressCallback, 'Prioritizer', 4, 6, 'completed');
        }

        // 5) Decision maker
        if ($settings->isBypassed('DecisionMaker')) {
            $this->notifyProgress($progressCallback, 'DecisionMaker', 5, 6, 'bypassed');
            Log::info('Workflow step: DecisionMaker bypassed', ['step' => 5, 'total_steps' => 6]);
        } else {
            $this->notifyProgress($progressCallback, 'DecisionMaker', 5, 6, 'processing');
            Log::info('Workflow step: DecisionMaker', ['step' => 5, 'total_steps' => 6]);
            $state = $this->getAgent(DecisionMakerAgent::class, DecisionMakerNeuronAgent::class)->handle($state);
            $this->notifyProgress($progressCallback, 'DecisionMaker', 5, 6, 'completed');
        }

        // 6) Linear writer
        if ($state->shouldEscalate) {
            if ($settings->isBypassed('LinearWriter')) {
                $this->notifyProgress($progressCallback, 'LinearWriter', 6, 6, 'bypassed');
                Log::info('Workflow step: LinearWriter bypassed', ['step' => 6, 'total_steps' => 6, 'reason' => 'shouldEscalate=true but agent bypassed']);
                $status = 'escalated';
            } else {
                $this->notifyProgress($progressCallback, 'LinearWriter', 6, 6, 'processing');
                Log::info('Workflow step: LinearWriter', ['step' => 6, 'total_steps' => 6, 'reason' => 'shouldEscalate=true']);
                $state = $this->getAgent(LinearWriterAgent::class, LinearWriterNeuronAgent::class)->handle($state);
                $this->notifyProgress($progressCallback, 'LinearWriter', 6, 6, 'completed');
                $status = 'escalated';
            }
        } else {
            $this->notifyProgress($progressCallback, 'LinearWriter', 6, 6, 'skipped');
            Log::info('Workflow step: Skipping LinearWriter', ['step' => 6, 'total_steps' => 6, 'reason' => 'shouldEscalate=false']);
            $status = 'processed';
        }

        $workflowTime = (microtime(true) - $workflowStartTime) * 1000;
        $run = $this->persistIfAvailable($text, $state, $status);

        Log::info('Workflow completed', [
            'status' => $status,
            'total_execution_time_ms' => round($workflowTime, 2),
            'run_id' => $run?->id,
            'linear_issue_id' => $state->linearIssueId,
            'linear_issue_url' => $state->linearIssueUrl,
        ]);

        // Delay al final per poder veure el resultat abans que es recarregui la pàgina
        $this->delay();

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

    /**
     * Run workflow as a generator that yields events in real-time.
     * This allows for true streaming of progress events.
     *
     * @return \Generator<string, array>
     */
    public function runStreaming(string $text): \Generator
    {
        $workflowStartTime = microtime(true);
        $inputLength = mb_strlen($text);

        Log::info('Workflow started (streaming)', [
            'input_length' => $inputLength,
            'input_preview' => mb_substr($text, 0, 200),
            'llm_enabled' => NeuronConfig::shouldUseLLM(),
            'llm_configured' => NeuronConfig::isConfigured(),
        ]);

        $state = new IncidentState($text);
        $settings = AppSetting::get();

        // 1) Interpreter
        if ($settings->isBypassed('Interpreter')) {
            yield 'agent-progress' => ['agent' => 'Interpreter', 'step' => 1, 'totalSteps' => 6, 'status' => 'bypassed'];
            Log::info('Workflow step: Interpreter bypassed', ['step' => 1, 'total_steps' => 6]);
        } else {
            yield 'agent-progress' => ['agent' => 'Interpreter', 'step' => 1, 'totalSteps' => 6, 'status' => 'processing'];
            Log::info('Workflow step: Interpreter', ['step' => 1, 'total_steps' => 6]);
            $state = $this->getAgent(InterpreterAgent::class, InterpreterNeuronAgent::class)->handle($state);
            yield 'agent-progress' => ['agent' => 'Interpreter', 'step' => 1, 'totalSteps' => 6, 'status' => 'completed'];
        }

        // 2) Classifier
        if ($settings->isBypassed('Classifier')) {
            yield 'agent-progress' => ['agent' => 'Classifier', 'step' => 2, 'totalSteps' => 6, 'status' => 'bypassed'];
            Log::info('Workflow step: Classifier bypassed', ['step' => 2, 'total_steps' => 6]);
        } else {
            yield 'agent-progress' => ['agent' => 'Classifier', 'step' => 2, 'totalSteps' => 6, 'status' => 'processing'];
            Log::info('Workflow step: Classifier', ['step' => 2, 'total_steps' => 6]);
            $state = $this->getAgent(ClassifierAgent::class, ClassifierNeuronAgent::class)->handle($state);
            yield 'agent-progress' => ['agent' => 'Classifier', 'step' => 2, 'totalSteps' => 6, 'status' => 'completed'];
        }

        // 3) Validator
        if ($settings->isBypassed('Validator')) {
            yield 'agent-progress' => ['agent' => 'Validator', 'step' => 3, 'totalSteps' => 6, 'status' => 'bypassed'];
            Log::info('Workflow step: Validator bypassed', ['step' => 3, 'total_steps' => 6]);
        } else {
            yield 'agent-progress' => ['agent' => 'Validator', 'step' => 3, 'totalSteps' => 6, 'status' => 'processing'];
            Log::info('Workflow step: Validator', ['step' => 3, 'total_steps' => 6]);
            $state = $this->getAgent(ValidatorAgent::class, ValidatorNeuronAgent::class)->handle($state);
            yield 'agent-progress' => ['agent' => 'Validator', 'step' => 3, 'totalSteps' => 6, 'status' => 'completed'];
        }

        if (! $state->isSufficient) {
            $status = 'needs_more_info';
            $workflowTime = (microtime(true) - $workflowStartTime) * 1000;
            $run = $this->persistIfAvailable($text, $state, $status);

            Log::info('Workflow completed (needs more info)', [
                'status' => $status,
                'total_execution_time_ms' => round($workflowTime, 2),
                'missing_info' => $state->missingInfo,
            ]);

            // Delay al final per poder veure el resultat
            $this->delay();

            yield 'workflow-result' => [
                'status' => $status,
                'state' => $state->toArray(),
                'run_id' => $run?->id,
            ];

            return;
        }

        // 4) Prioritizer
        if ($settings->isBypassed('Prioritizer')) {
            yield 'agent-progress' => ['agent' => 'Prioritizer', 'step' => 4, 'totalSteps' => 6, 'status' => 'bypassed'];
            Log::info('Workflow step: Prioritizer bypassed', ['step' => 4, 'total_steps' => 6]);
        } else {
            yield 'agent-progress' => ['agent' => 'Prioritizer', 'step' => 4, 'totalSteps' => 6, 'status' => 'processing'];
            Log::info('Workflow step: Prioritizer', ['step' => 4, 'total_steps' => 6]);
            $state = $this->getAgent(PrioritizerAgent::class, PrioritizerNeuronAgent::class)->handle($state);
            yield 'agent-progress' => ['agent' => 'Prioritizer', 'step' => 4, 'totalSteps' => 6, 'status' => 'completed'];
        }

        // 5) Decision maker
        if ($settings->isBypassed('DecisionMaker')) {
            yield 'agent-progress' => ['agent' => 'DecisionMaker', 'step' => 5, 'totalSteps' => 6, 'status' => 'bypassed'];
            Log::info('Workflow step: DecisionMaker bypassed', ['step' => 5, 'total_steps' => 6]);
        } else {
            yield 'agent-progress' => ['agent' => 'DecisionMaker', 'step' => 5, 'totalSteps' => 6, 'status' => 'processing'];
            Log::info('Workflow step: DecisionMaker', ['step' => 5, 'total_steps' => 6]);
            $state = $this->getAgent(DecisionMakerAgent::class, DecisionMakerNeuronAgent::class)->handle($state);
            yield 'agent-progress' => ['agent' => 'DecisionMaker', 'step' => 5, 'totalSteps' => 6, 'status' => 'completed'];
        }

        // 6) Linear writer (si procedeix)
        if ($state->shouldEscalate) {
            if ($settings->isBypassed('LinearWriter')) {
                yield 'agent-progress' => ['agent' => 'LinearWriter', 'step' => 6, 'totalSteps' => 6, 'status' => 'bypassed'];
                Log::info('Workflow step: LinearWriter bypassed', ['step' => 6, 'total_steps' => 6, 'reason' => 'shouldEscalate=true but agent bypassed']);
                $status = 'escalated';
            } else {
                yield 'agent-progress' => ['agent' => 'LinearWriter', 'step' => 6, 'totalSteps' => 6, 'status' => 'processing'];
                Log::info('Workflow step: LinearWriter', ['step' => 6, 'total_steps' => 6, 'reason' => 'shouldEscalate=true']);
                $state = $this->getAgent(LinearWriterAgent::class, LinearWriterNeuronAgent::class)->handle($state);
                yield 'agent-progress' => ['agent' => 'LinearWriter', 'step' => 6, 'totalSteps' => 6, 'status' => 'completed'];
                $status = 'escalated';
            }
        } else {
            yield 'agent-progress' => ['agent' => 'LinearWriter', 'step' => 6, 'totalSteps' => 6, 'status' => 'skipped'];
            Log::info('Workflow step: Skipping LinearWriter', ['step' => 6, 'total_steps' => 6, 'reason' => 'shouldEscalate=false']);
            $status = 'processed';
        }

        $workflowTime = (microtime(true) - $workflowStartTime) * 1000;
        $run = $this->persistIfAvailable($text, $state, $status);

        Log::info('Workflow completed', [
            'status' => $status,
            'total_execution_time_ms' => round($workflowTime, 2),
            'run_id' => $run?->id,
            'linear_issue_id' => $state->linearIssueId,
            'linear_issue_url' => $state->linearIssueUrl,
        ]);

        // Delay al final per poder veure el resultat abans que es recarregui la pàgina
        $this->delay();

        yield 'workflow-result' => [
            'status' => $status,
            'run_id' => $run?->id,
            'state' => $state->toArray(),
        ];
    }

    private function notifyProgress(?callable $callback, string $agentName, int $step, int $totalSteps, string $status): void
    {
        if ($callback !== null) {
            $callback($agentName, $step, $totalSteps, $status);
        }
    }

    private function delay(): void
    {
        // Avoid artificial delays when running automated tests
        if (app()->runningUnitTests()) {
            return;
        }

        usleep(500000);
    }
}
