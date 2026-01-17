<?php

use App\AI\Orchestrator\IncidentWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

test('processes incident and returns status with heuristic agents', function () {
    // Ensure LLM is disabled for this test
    Config::set('neuron-ai.use_llm', false);

    $workflow = app(IncidentWorkflow::class);
    $result = $workflow->run('El POS falla en cobrar. Crash quan premo pagar. iOS 14.8. Passos: obrir comanda, pagar, peta.');

    expect($result)->toHaveKeys(['status', 'state'])
        ->and($result['state'])->toHaveKeys(['rawText', 'summary', 'type', 'area', 'trace'])
        ->and($result['state']['type'])->toBe('bug')
        ->and($result['state']['area'])->toBe('pos');
});

test('api endpoint processes incident correctly', function () {
    // Ensure LLM is disabled for this test
    Config::set('neuron-ai.use_llm', false);

    $response = $this->postJson('/api/incidents/process', [
        'text' => 'El KDS no mostra les comandes. Error intermitent.',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'state' => [
                'rawText',
                'summary',
                'type',
                'area',
                'trace',
            ],
        ]);
});

test('api endpoint returns error when text is missing', function () {
    $response = $this->postJson('/api/incidents/process', []);

    $response->assertStatus(422)
        ->assertJson(['error' => 'Missing text']);
});
