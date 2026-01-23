<?php

namespace App\Http\Controllers;

use App\AI\Orchestrator\IncidentWorkflow;
use App\Integrations\Linear\LinearClient;
use App\Integrations\Linear\LinearMapper;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedEvent;
use Illuminate\Support\Facades\Cache;

class SupportTicketController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $tab = $request->query('tab', 'pending');

        $query = SupportTicket::query();

        // Filter by tab
        if ($tab === 'needs_review') {
            $query->where('status', 'in_review');
        } elseif ($tab === 'completed') {
            $query->where('status', 'processed');
        } else {
            // pending: status is 'new' or null
            $query->where(function ($q) {
                $q->where('status', 'new')
                    ->orWhereNull('status');
            });
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Severity filter
        if ($request->filled('severity')) {
            $query->where('severity', $request->query('severity'));
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        // Product filter
        if ($request->filled('product')) {
            $query->where('product', $request->query('product'));
        }

        // Eager load incident runs (most recent first) for review info
        $query->with(['incidentRuns' => function ($q) {
            $q->latest()->limit(1);
        }]);

        $tickets = $query->latest()->paginate(15)->withQueryString();

        // Calculate ticket counts for each tab (without search/filter filters)
        $pendingCount = SupportTicket::where(function ($q) {
            $q->where('status', 'new')
                ->orWhereNull('status');
        })->count();

        $needsReviewCount = SupportTicket::where('status', 'in_review')->count();

        $completedCount = SupportTicket::where('status', 'processed')->count();

        return view('support.index', [
            'tickets' => $tickets,
            'activeTab' => $tab,
            'search' => $request->query('search'),
            'severity' => $request->query('severity'),
            'priority' => $request->query('priority'),
            'product' => $request->query('product'),
            'pendingCount' => $pendingCount,
            'needsReviewCount' => $needsReviewCount,
            'completedCount' => $completedCount,
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

    public function processStream(string $id, Request $request, IncidentWorkflow $workflow)
    {
        $ticket = SupportTicket::find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        $text = $ticket->description ?? $ticket->title ?? '';

        if (trim($text) === '') {
            return response()->json(['error' => 'Ticket has no valid description'], 400);
        }

        return response()->eventStream(function () use ($workflow, $text, $ticket, $id) {
            try {
                $result = null;

                // Use the streaming generator to yield events in real-time
                foreach ($workflow->runStreaming($text) as $eventType => $eventData) {
                    if ($eventType === 'agent-progress') {
                        yield new StreamedEvent(
                            event: 'agent-progress',
                            data: json_encode($eventData)
                        );
                    } elseif ($eventType === 'workflow-result') {
                        $result = $eventData;
                    }
                }

                // Update ticket status based on workflow result
                if (! $result) {
                    throw new \RuntimeException('Workflow did not return a result');
                }

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

                // Store workflow result in cache for the agents view
                // Use cache instead of session because session may not persist during stream
                $cacheKey = "workflow_result_{$id}";
                Cache::put($cacheKey, $result, now()->addMinutes(10));

                // Emit completion event
                yield new StreamedEvent(
                    event: 'workflow-complete',
                    data: json_encode([
                        'status' => $status,
                        'redirectUrl' => route('support.agents', $id),
                    ])
                );
            } catch (\Exception $e) {
                yield new StreamedEvent(
                    event: 'workflow-error',
                    data: json_encode([
                        'error' => $e->getMessage(),
                    ])
                );
            }
        });
    }

    public function agents(string $id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $ticket = SupportTicket::find($id);

        if (! $ticket) {
            return redirect()->route('support.index')
                ->with('error', 'Ticket not found');
        }

        // Try to get workflow result from cache first, then from session
        $cacheKey = "workflow_result_{$id}";
        $workflowResult = Cache::get($cacheKey) ?? session('workflow_result');

        // Clear cache after retrieving
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
        }

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

        // Redirect to appropriate tab based on result
        $redirectTab = 'pending';
        if ($processed > 0) {
            // Check if any ticket ended up in review
            $reviewCount = SupportTicket::whereIn('id', $ticketIds)
                ->where('status', 'in_review')
                ->count();
            if ($reviewCount > 0) {
                $redirectTab = 'needs_review';
            } else {
                $redirectTab = 'completed';
            }
        }

        return redirect()->route('support.index', ['tab' => $redirectTab])
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
