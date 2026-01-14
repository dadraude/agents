<?php

namespace App\AI\Orchestrator;

class IncidentState
{
    public string $rawText;

    // Interpreter
    public ?string $summary = null;

    public ?string $intent = null;

    public array $entities = [];

    // Classifier
    public ?string $type = null;        // bug | question | feature | other

    public ?string $area = null;        // pos | kds | backoffice | loyalty | infra | other

    public ?bool $devRelated = null;

    // Validator
    public bool $isSufficient = true;

    public array $missingInfo = [];     // ["steps_to_reproduce", "environment_version", ...]

    // Prioritizer
    public ?int $impact = null;         // 1-5

    public ?int $urgency = null;        // 1-5

    public ?int $severity = null;       // 1-5

    public ?float $priorityScore = null;

    // Decision
    public bool $shouldEscalate = false;

    public ?string $decisionReason = null;

    // Linear
    public ?string $linearIssueId = null;

    public ?string $linearIssueUrl = null;

    // Trace
    public array $trace = [];

    public function __construct(string $rawText)
    {
        $this->rawText = $rawText;
    }

    public function addTrace(string $agentName, array $data): void
    {
        $this->trace[] = [
            'agent' => $agentName,
            'data' => $data,
            'ts' => date('c'),
        ];
    }

    public function toArray(): array
    {
        return [
            'rawText' => $this->rawText,

            'summary' => $this->summary,
            'intent' => $this->intent,
            'entities' => $this->entities,

            'type' => $this->type,
            'area' => $this->area,
            'devRelated' => $this->devRelated,

            'isSufficient' => $this->isSufficient,
            'missingInfo' => $this->missingInfo,

            'impact' => $this->impact,
            'urgency' => $this->urgency,
            'severity' => $this->severity,
            'priorityScore' => $this->priorityScore,

            'shouldEscalate' => $this->shouldEscalate,
            'decisionReason' => $this->decisionReason,

            'linearIssueId' => $this->linearIssueId,
            'linearIssueUrl' => $this->linearIssueUrl,

            'trace' => $this->trace,
        ];
    }
}
