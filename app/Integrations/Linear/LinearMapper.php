<?php

namespace App\Integrations\Linear;

use App\AI\Orchestrator\IncidentState;
use App\Models\SupportTicket;

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

    public function mapTicketToIssuePayload(SupportTicket $ticket): array
    {
        $teamId = config('services.linear.team_id');

        $titlePrefix = strtoupper($ticket->severity ?? 'TICKET');
        $title = "{$titlePrefix}: ".($ticket->title ?? 'Support Ticket');

        $description = $this->buildTicketDescription($ticket);

        $payload = [
            'title' => $title,
            'description' => $description,
        ];

        if ($teamId) {
            $payload['teamId'] = $teamId;
        }

        return $payload;
    }

    private function buildTicketDescription(SupportTicket $ticket): string
    {
        $lines = [];
        $lines[] = '## Description';
        $lines[] = $ticket->description ?? '(none)';
        $lines[] = '';
        $lines[] = '## Ticket Information';
        $lines[] = '- Ticket ID: '.$ticket->id;
        $lines[] = '- Status: '.ucfirst($ticket->status ?? 'unknown');
        $lines[] = '- Severity: '.ucfirst($ticket->severity ?? 'normal');
        $lines[] = '- Priority: '.ucfirst($ticket->priority ?? 'normal');
        $lines[] = '- Product: '.strtoupper($ticket->product ?? 'N/A');
        $lines[] = '- Channel: '.ucfirst($ticket->channel ?? 'N/A');
        $lines[] = '';

        if ($ticket->customer_name || $ticket->customer_email) {
            $lines[] = '## Customer';
            if ($ticket->customer_name) {
                $lines[] = '- Name: '.$ticket->customer_name;
            }
            if ($ticket->customer_email) {
                $lines[] = '- Email: '.$ticket->customer_email;
            }
            if ($ticket->customer_phone) {
                $lines[] = '- Phone: '.$ticket->customer_phone;
            }
            $lines[] = '';
        }

        if ($ticket->environment_device || $ticket->environment_os || $ticket->environment_app_version) {
            $lines[] = '## Environment';
            if ($ticket->environment_device) {
                $lines[] = '- Device: '.$ticket->environment_device;
            }
            if ($ticket->environment_os) {
                $lines[] = '- OS: '.$ticket->environment_os;
            }
            if ($ticket->environment_app_version) {
                $lines[] = '- App Version: '.$ticket->environment_app_version;
            }
            $lines[] = '';
        }

        if ($ticket->sla_deadline) {
            $lines[] = '## SLA';
            $lines[] = '- Deadline: '.$ticket->sla_deadline->format('Y-m-d H:i:s');
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}
