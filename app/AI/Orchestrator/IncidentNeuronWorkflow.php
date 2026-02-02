<?php

namespace App\AI\Orchestrator;

use App\AI\Orchestrator\Nodes\AfterDecisionNode;
use App\AI\Orchestrator\Nodes\AfterValidatorNode;
use App\AI\Orchestrator\Nodes\ClassifierNode;
use App\AI\Orchestrator\Nodes\DecisionMakerNode;
use App\AI\Orchestrator\Nodes\InterpreterNode;
use App\AI\Orchestrator\Nodes\LinearWriterNode;
use App\AI\Orchestrator\Nodes\PrioritizerNode;
use App\AI\Orchestrator\Nodes\ValidatorNode;
use NeuronAI\Workflow\Workflow;
use NeuronAI\Workflow\WorkflowState;

class IncidentNeuronWorkflow extends Workflow
{
    public function __construct(?WorkflowState $state = null)
    {
        parent::__construct($state);

        // Register all nodes in the workflow
        $this->addNodes([
            new InterpreterNode,
            new ClassifierNode,
            new ValidatorNode,
            new AfterValidatorNode,
            new PrioritizerNode,
            new DecisionMakerNode,
            new AfterDecisionNode,
            new LinearWriterNode,
        ]);
    }

    public static function makeForText(string $text): self
    {
        $state = new WorkflowState;
        $incidentState = new IncidentState($text);
        $state->set('incident', $incidentState->toArray());
        $state->set('rawText', $text);

        return new self($state);
    }
}
