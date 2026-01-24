<?php

use App\AI\Orchestrator\IncidentWorkflow;
use App\Models\SupportTicket;
use Database\Seeders\SupportTicketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

test('most seeded tickets progress beyond needs_more_info', function () {
    // Ensure we use heuristic agents and avoid external LLM calls
    config()->set('neuron-ai.use_llm', false);

    seed(SupportTicketSeeder::class);

    $workflow = app(IncidentWorkflow::class);

    $total = 0;
    $needsMoreInfo = 0;
    $processedOrEscalated = 0;

    /** @var \App\Models\SupportTicket $ticket */
    foreach (SupportTicket::all() as $ticket) {
        $result = $workflow->run($ticket->description);
        $status = $result['status'] ?? 'unknown';

        $total++;

        if ($status === 'needs_more_info') {
            $needsMoreInfo++;
        }

        if (in_array($status, ['processed', 'escalated'], true)) {
            $processedOrEscalated++;
        }
    }

    expect($total)->toBeGreaterThan(0);

    // We want at least ~70% of seeded tickets to progress.
    $ratio = $processedOrEscalated / $total;
    expect($ratio)->toBeGreaterThanOrEqual(0.7);
});
