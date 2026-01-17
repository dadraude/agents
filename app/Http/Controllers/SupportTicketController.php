<?php

namespace App\Http\Controllers;

use App\AI\Orchestrator\IncidentWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SupportTicketController extends Controller
{
    private function loadTickets(): array
    {
        $path = database_path('fixtures/support_tickets.json');

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);

        return json_decode($content, true) ?? [];
    }

    private function findTicket(string $id): ?array
    {
        $tickets = $this->loadTickets();

        foreach ($tickets as $ticket) {
            if (($ticket['id'] ?? null) === $id) {
                return $ticket;
            }
        }

        return null;
    }

    public function index(): \Illuminate\Contracts\View\View
    {
        $tickets = $this->loadTickets();

        return view('support.index', [
            'tickets' => $tickets,
        ]);
    }

    public function show(string $id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $ticket = $this->findTicket($id);

        if (! $ticket) {
            return redirect()->route('support.index')
                ->with('error', 'Ticket no trobat');
        }

        return view('support.show', [
            'ticket' => $ticket,
        ]);
    }

    public function process(string $id, Request $request, IncidentWorkflow $workflow): \Illuminate\Http\RedirectResponse
    {
        $ticket = $this->findTicket($id);

        if (! $ticket) {
            return redirect()->route('support.index')
                ->with('error', 'Ticket no trobat');
        }

        $text = $ticket['description'] ?? $ticket['title'] ?? '';

        if (trim($text) === '') {
            return redirect()->route('support.show', $id)
                ->with('error', 'El ticket no té descripció vàlida');
        }

        $result = $workflow->run($text);

        return redirect()->route('support.agents', $id)
            ->with('workflow_result', $result);
    }

    public function agents(string $id): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $ticket = $this->findTicket($id);

        if (! $ticket) {
            return redirect()->route('support.index')
                ->with('error', 'Ticket no trobat');
        }

        $workflowResult = session('workflow_result');

        return view('support.agents', [
            'ticket' => $ticket,
            'workflowResult' => $workflowResult,
        ]);
    }
}
