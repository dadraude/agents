<?php

namespace App\Integrations\Linear;

use App\AI\Orchestrator\IncidentState;

class LinearMapper
{
    public function mapStateToIssuePayload(IncidentState $state): array
    {
        $teamId = config('services.linear.team_id');

        $titlePrefix = strtoupper($state->type ?? 'INC');
        $title = "{$titlePrefix}: ".($state->summary ?? 'Incident');

        $description = $this->buildDescription($state);

        $payload = [
            'title' => $title,
            'description' => $description,
        ];

        if ($teamId) {
            $payload['teamId'] = $teamId;
        }

        return $payload;
    }

    private function buildDescription(IncidentState $state): string
    {
        $lines = [];
        $lines[] = '## Summary';
        $lines[] = $state->summary ?? '(none)';
        $lines[] = '';
        $lines[] = '## Raw';
        $lines[] = $state->rawText;
        $lines[] = '';
        $lines[] = '## Classification';
        $lines[] = '- Type: '.($state->type ?? 'n/a');
        $lines[] = '- Area: '.($state->area ?? 'n/a');
        $lines[] = '- Dev related: '.(($state->devRelated === true) ? 'yes' : 'no');
        $lines[] = '';
        $lines[] = '## Priority';
        $lines[] = '- Impact: '.($state->impact ?? 'n/a');
        $lines[] = '- Urgency: '.($state->urgency ?? 'n/a');
        $lines[] = '- Severity: '.($state->severity ?? 'n/a');
        $lines[] = '- Score: '.($state->priorityScore ?? 'n/a');
        $lines[] = '';
        $lines[] = '## Decision';
        $lines[] = '- Escalate: '.($state->shouldEscalate ? 'yes' : 'no');
        $lines[] = '- Reason: '.($state->decisionReason ?? 'n/a');

        return implode("\n", $lines);
    }
}
