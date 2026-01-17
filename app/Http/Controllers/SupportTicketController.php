<?php

namespace App\Http\Controllers;

use App\AI\Orchestrator\IncidentWorkflow;
use App\Integrations\Linear\LinearClient;
use App\Integrations\Linear\LinearMapper;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $tab = $request->query('tab', 'pending');

        $query = SupportTicket::query();

        if ($tab === 'processed') {
            $query->where('status', 'processed');
        } else {
            $query->where(function ($q) {
                $q->where('status', '!=', 'processed')
                    ->orWhereNull('status');
            });
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        return view('support.index', [
            'tickets' => $tickets,
            'activeTab' => $tab,
        ]);
    }

    public function show(string $id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $ticket = SupportTicket::find($id);

        if (! $ticket) {
            return redirect()->route('support.index')
                ->with('error', 'Ticket not found');
        }

        return view('support.show', [
            'ticket' => $ticket,
        ]);
    }

    public function process(string $id, Request $request, IncidentWorkflow $workflow): \Illuminate\Http\RedirectResponse
    {
        $ticket = SupportTicket::find($id);

        if (! $ticket) {
            return redirect()->route('support.index')
                ->with('error', 'Ticket not found');
        }

        $text = $ticket->description ?? $ticket->title ?? '';

        if (trim($text) === '') {
            return redirect()->route('support.show', $id)
                ->with('error', 'Ticket has no valid description');
        }

        try {
            $result = $workflow->run($text);

            // Update ticket status based on workflow result
            $status = $result['status'] ?? null;
            $state = $result['state'] ?? [];

            if ($status === 'processed') {
                $ticket->status = 'processed';
            } elseif ($status === 'escalated') {
                $ticket->status = 'in_review';
            } elseif ($status === 'needs_more_info') {
                $ticket->status = 'in_review';
            }

            // Save Linear URL if exists
            if (isset($state['linearIssueUrl']) && ! empty($state['linearIssueUrl'])) {
                $ticket->linear_issue_url = $state['linearIssueUrl'];
                $ticket->linear_issue_id = $state['linearIssueId'] ?? null;
            }

            $ticket->save();

            // Relate IncidentRun to ticket if created
            if (isset($result['run_id']) && $result['run_id']) {
                $incidentRun = \App\Models\IncidentRun::find($result['run_id']);
                if ($incidentRun) {
                    $incidentRun->support_ticket_id = $ticket->id;
                    $incidentRun->save();
                }
            }

            return redirect()->route('support.agents', $id)
                ->with('workflow_result', $result);
        } catch (\Exception $e) {
            return redirect()->route('support.show', $id)
                ->with('error', 'Error processing ticket: '.$e->getMessage());
        }
    }

    public function agents(string $id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $ticket = SupportTicket::find($id);

        if (! $ticket) {
            return redirect()->route('support.index')
                ->with('error', 'Ticket not found');
        }

        $workflowResult = session('workflow_result');

        return view('support.agents', [
            'ticket' => $ticket,
            'workflowResult' => $workflowResult,
        ]);
    }

    public function processBatch(Request $request, IncidentWorkflow $workflow): \Illuminate\Http\RedirectResponse
    {
        $ticketIds = $request->input('ticket_ids', []);

        if (empty($ticketIds)) {
            return redirect()->route('support.index')
                ->with('error', 'No tickets selected for processing');
        }

        $tickets = SupportTicket::whereIn('id', $ticketIds)->get();

        if ($tickets->isEmpty()) {
            return redirect()->route('support.index')
                ->with('error', 'No valid tickets found');
        }

        $processed = 0;
        $errors = [];

        foreach ($tickets as $ticket) {
            $text = $ticket->description ?? $ticket->title ?? '';

            if (trim($text) === '') {
                $errors[] = "Ticket {$ticket->id} has no valid description";

                continue;
            }

            try {
                $result = $workflow->run($text);

                $status = $result['status'] ?? null;
                $state = $result['state'] ?? [];

                if ($status === 'processed') {
                    $ticket->status = 'processed';
                } elseif ($status === 'escalated') {
                    $ticket->status = 'in_review';
                } elseif ($status === 'needs_more_info') {
                    $ticket->status = 'in_review';
                }

                $ticket->save();

                if (isset($result['run_id']) && $result['run_id']) {
                    $incidentRun = \App\Models\IncidentRun::find($result['run_id']);
                    if ($incidentRun) {
                        $incidentRun->support_ticket_id = $ticket->id;
                        $incidentRun->save();
                    }
                }

                $processed++;
            } catch (\Exception $e) {
                $errors[] = "Error processing ticket {$ticket->id}: ".$e->getMessage();
            }
        }

        $message = "Successfully processed {$processed} ticket(s).";
        if (! empty($errors)) {
            $message .= ' Errors: '.implode('; ', $errors);
        }

        return redirect()->route('support.index', ['tab' => 'processed'])
            ->with($errors ? 'error' : 'success', $message);
    }

    public function createLinear(string $id, Request $request, LinearClient $client, LinearMapper $mapper): \Illuminate\Http\RedirectResponse
    {
        $ticket = SupportTicket::find($id);

        if (! $ticket) {
            return redirect()->route('support.index')
                ->with('error', 'Ticket not found');
        }

        if (! $client->isConfigured()) {
            return redirect()->route('support.show', $id)
                ->with('error', 'Linear API is not configured');
        }

        if ($ticket->linear_issue_url) {
            return redirect()->route('support.show', $id)
                ->with('info', 'This ticket already has a Linear issue created.');
        }

        try {
            $payload = $mapper->mapTicketToIssuePayload($ticket);
            $issue = $client->createIssue($payload);

            if (isset($issue['error']) && $issue['error']) {
                return redirect()->route('support.show', $id)
                    ->with('error', 'Failed to create Linear issue: '.($issue['body']['errors'][0]['message'] ?? 'Unknown error'));
            }

            $ticket->linear_issue_id = $issue['id'] ?? null;
            $ticket->linear_issue_url = $issue['url'] ?? null;
            $ticket->save();

            return redirect()->route('support.show', $id)
                ->with('success', 'Linear issue created successfully!');
        } catch (\Exception $e) {
            return redirect()->route('support.show', $id)
                ->with('error', 'Error creating Linear issue: '.$e->getMessage());
        }
    }
}
