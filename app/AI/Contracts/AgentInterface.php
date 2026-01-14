<?php

namespace App\AI\Contracts;

use App\AI\Orchestrator\IncidentState;

interface AgentInterface
{
    public function handle(IncidentState $state): IncidentState;

    public function name(): string;
}
