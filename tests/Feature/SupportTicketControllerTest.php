<?php

use App\AI\Orchestrator\IncidentWorkflow;
use App\Integrations\Linear\LinearClient;
use App\Integrations\Linear\LinearMapper;
use App\Models\SupportTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

test('index displays all tickets', function () {
    $ticket1 = SupportTicket::factory()->create(['id' => 'TKT-001', 'title' => 'Ticket 1']);
    $ticket2 = SupportTicket::factory()->create(['id' => 'TKT-002', 'title' => 'Ticket 2']);

    $response = $this->get(route('support.index'));

    $response->assertSuccessful()
        ->assertSee('Ticket 1')
        ->assertSee('Ticket 2');
});

test('index displays empty state when no tickets', function () {
    $response = $this->get(route('support.index'));

    $response->assertSuccessful()
        ->assertSee('No tickets available');
});

test('show displays ticket details', function () {
    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
        'description' => 'Test Description',
        'status' => 'new',
        'severity' => 'high',
    ]);

    $response = $this->get(route('support.show', $ticket->id));

    $response->assertSuccessful()
        ->assertSee('Test Ticket')
        ->assertSee('Test Description')
        ->assertSee('New')
        ->assertSee('High');
});

test('show redirects to index when ticket not found', function () {
    $response = $this->get(route('support.show', 'NONEXISTENT'));

    $response->assertRedirect(route('support.index'))
        ->assertSessionHas('error', 'Ticket not found');
});

test('process runs workflow and redirects to agents page', function () {
    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
        'description' => 'El POS falla en cobrar.',
    ]);

    $workflowMock = mock(IncidentWorkflow::class);
    $workflowMock->shouldReceive('run')
        ->once()
        ->with('El POS falla en cobrar.')
        ->andReturn([
            'status' => 'processed',
            'run_id' => null,
            'state' => [
                'rawText' => 'El POS falla en cobrar.',
                'summary' => 'POS payment issue',
                'type' => 'bug',
                'area' => 'pos',
                'trace' => [],
            ],
        ]);

    $this->app->instance(IncidentWorkflow::class, $workflowMock);

    $response = $this->post(route('support.process', $ticket->id));

    $response->assertRedirect(route('support.agents', $ticket->id))
        ->assertSessionHas('workflow_result');
});

test('process updates ticket status after workflow', function () {
    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
        'description' => 'El POS falla en cobrar.',
        'status' => 'new',
    ]);

    $workflowMock = mock(IncidentWorkflow::class);
    $workflowMock->shouldReceive('run')
        ->once()
        ->andReturn([
            'status' => 'processed',
            'run_id' => null,
            'state' => [
                'rawText' => 'El POS falla en cobrar.',
                'summary' => 'POS payment issue',
                'type' => 'bug',
                'area' => 'pos',
                'trace' => [],
            ],
        ]);

    $this->app->instance(IncidentWorkflow::class, $workflowMock);

    $this->post(route('support.process', $ticket->id));

    $ticket->refresh();
    expect($ticket->status)->toBe('processed');
});

test('process updates ticket status to in_review when escalated', function () {
    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
        'description' => 'Critical issue.',
        'status' => 'new',
    ]);

    $workflowMock = mock(IncidentWorkflow::class);
    $workflowMock->shouldReceive('run')
        ->once()
        ->andReturn([
            'status' => 'escalated',
            'run_id' => null,
            'state' => [
                'rawText' => 'Critical issue.',
                'summary' => 'Critical',
                'type' => 'bug',
                'area' => 'pos',
                'trace' => [],
                'linearIssueUrl' => 'https://linear.app/issue/123',
                'linearIssueId' => 'linear-issue-id-123',
            ],
        ]);

    $this->app->instance(IncidentWorkflow::class, $workflowMock);

    $this->post(route('support.process', $ticket->id));

    $ticket->refresh();
    expect($ticket->status)->toBe('in_review');
    expect($ticket->linear_issue_url)->toBe('https://linear.app/issue/123');
    expect($ticket->linear_issue_id)->toBe('linear-issue-id-123');
});

test('process redirects when ticket not found', function () {
    $response = $this->post(route('support.process', 'NONEXISTENT'));

    $response->assertRedirect(route('support.index'))
        ->assertSessionHas('error', 'Ticket not found');
});

test('process redirects when ticket has no description', function () {
    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
        'description' => '',
    ]);

    $response = $this->post(route('support.process', $ticket->id));

    $response->assertRedirect(route('support.show', $ticket->id))
        ->assertSessionHas('error', 'Ticket has no valid description');
});

test('process handles workflow errors gracefully', function () {
    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
        'description' => 'Test description',
    ]);

    $workflowMock = mock(IncidentWorkflow::class);
    $workflowMock->shouldReceive('run')
        ->once()
        ->andThrow(new \Exception('Workflow error'));

    $this->app->instance(IncidentWorkflow::class, $workflowMock);

    $response = $this->post(route('support.process', $ticket->id));

    $response->assertRedirect(route('support.show', $ticket->id))
        ->assertSessionHas('error');
});

test('agents displays workflow results', function () {
    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
    ]);

    $workflowResult = [
        'status' => 'processed',
        'state' => [
            'rawText' => 'Test',
            'summary' => 'Test summary',
            'type' => 'bug',
            'area' => 'pos',
            'trace' => [
                [
                    'agent' => 'Interpreter',
                    'ts' => now()->toIso8601String(),
                    'data' => ['key' => 'value'],
                ],
            ],
        ],
    ];

    $response = $this->withSession(['workflow_result' => $workflowResult])
        ->get(route('support.agents', $ticket->id));

    $response->assertSuccessful()
        ->assertSee('Processed')
        ->assertSee('Interpreter');
});

test('agents displays message when no workflow results', function () {
    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
    ]);

    $response = $this->get(route('support.agents', $ticket->id));

    $response->assertSuccessful()
        ->assertSee('No workflow results available');
});

test('agents redirects when ticket not found', function () {
    $response = $this->get(route('support.agents', 'NONEXISTENT'));

    $response->assertRedirect(route('support.index'))
        ->assertSessionHas('error', 'Ticket not found');
});

test('createLinear creates Linear issue successfully', function () {
    Config::set('services.linear.api_key', 'test-api-key');

    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
        'description' => 'Test description',
        'severity' => 'high',
    ]);

    $clientMock = mock(LinearClient::class);
    $clientMock->shouldReceive('isConfigured')
        ->once()
        ->andReturn(true);
    $clientMock->shouldReceive('createIssue')
        ->once()
        ->andReturn([
            'id' => 'linear-issue-id',
            'url' => 'https://linear.app/issue/123',
            'identifier' => 'TKT-123',
        ]);

    $mapperMock = mock(LinearMapper::class);
    $mapperMock->shouldReceive('mapTicketToIssuePayload')
        ->once()
        ->with(\Mockery::type(SupportTicket::class))
        ->andReturn([
            'title' => 'HIGH: Test Ticket',
            'description' => 'Test description',
        ]);

    $this->app->instance(LinearClient::class, $clientMock);
    $this->app->instance(LinearMapper::class, $mapperMock);

    $response = $this->post(route('support.createLinear', $ticket->id));

    $response->assertRedirect(route('support.show', $ticket->id))
        ->assertSessionHas('success', 'Linear issue created successfully!');

    $ticket->refresh();
    expect($ticket->linear_issue_id)->toBe('linear-issue-id');
    expect($ticket->linear_issue_url)->toBe('https://linear.app/issue/123');
});

test('createLinear redirects when ticket not found', function () {
    $response = $this->post(route('support.createLinear', 'NONEXISTENT'));

    $response->assertRedirect(route('support.index'))
        ->assertSessionHas('error', 'Ticket not found');
});

test('createLinear redirects when Linear not configured', function () {
    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
    ]);

    $clientMock = mock(LinearClient::class);
    $clientMock->shouldReceive('isConfigured')
        ->once()
        ->andReturn(false);

    $this->app->instance(LinearClient::class, $clientMock);

    $response = $this->post(route('support.createLinear', $ticket->id));

    $response->assertRedirect(route('support.show', $ticket->id))
        ->assertSessionHas('error', 'Linear API is not configured');
});

test('createLinear shows info when Linear issue already exists', function () {
    Config::set('services.linear.api_key', 'test-api-key');

    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
        'linear_issue_url' => 'https://linear.app/issue/123',
    ]);

    $clientMock = mock(LinearClient::class);
    $clientMock->shouldReceive('isConfigured')
        ->once()
        ->andReturn(true);

    $this->app->instance(LinearClient::class, $clientMock);

    $response = $this->post(route('support.createLinear', $ticket->id));

    $response->assertRedirect(route('support.show', $ticket->id))
        ->assertSessionHas('info', 'This ticket already has a Linear issue created.');
});

test('createLinear handles Linear API errors gracefully', function () {
    Config::set('services.linear.api_key', 'test-api-key');

    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
        'description' => 'Test description',
    ]);

    $clientMock = mock(LinearClient::class);
    $clientMock->shouldReceive('isConfigured')
        ->once()
        ->andReturn(true);
    $clientMock->shouldReceive('createIssue')
        ->once()
        ->andReturn([
            'error' => true,
            'status' => 400,
            'body' => [
                'errors' => [
                    ['message' => 'Invalid request'],
                ],
            ],
        ]);

    $mapperMock = mock(LinearMapper::class);
    $mapperMock->shouldReceive('mapTicketToIssuePayload')
        ->once()
        ->andReturn([
            'title' => 'HIGH: Test Ticket',
            'description' => 'Test description',
        ]);

    $this->app->instance(LinearClient::class, $clientMock);
    $this->app->instance(LinearMapper::class, $mapperMock);

    $response = $this->post(route('support.createLinear', $ticket->id));

    $response->assertRedirect(route('support.show', $ticket->id))
        ->assertSessionHas('error');
});

test('createLinear handles exceptions gracefully', function () {
    Config::set('services.linear.api_key', 'test-api-key');

    $ticket = SupportTicket::factory()->create([
        'id' => 'TKT-001',
        'title' => 'Test Ticket',
    ]);

    $clientMock = mock(LinearClient::class);
    $clientMock->shouldReceive('isConfigured')
        ->once()
        ->andReturn(true);

    $mapperMock = mock(LinearMapper::class);
    $mapperMock->shouldReceive('mapTicketToIssuePayload')
        ->once()
        ->andThrow(new \Exception('Mapper error'));

    $this->app->instance(LinearClient::class, $clientMock);
    $this->app->instance(LinearMapper::class, $mapperMock);

    $response = $this->post(route('support.createLinear', $ticket->id));

    $response->assertRedirect(route('support.show', $ticket->id))
        ->assertSessionHas('error');
});
