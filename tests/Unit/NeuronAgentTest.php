<?php

use App\AI\Neuron\ClassifierNeuronAgent;
use App\AI\Neuron\InterpreterNeuronAgent;
use App\AI\Orchestrator\IncidentState;
use Illuminate\Support\Facades\Config;

test('InterpreterNeuronAgent falls back to heuristic when LLM disabled', function () {
    Config::set('neuron-ai.use_llm', false);

    $state = new IncidentState('El POS falla en cobrar.');
    $agent = app(InterpreterNeuronAgent::class);

    $result = $agent->handle($state);

    expect($result->summary)->not->toBeEmpty()
        ->and($result->intent)->not->toBeNull()
        ->and($result->entities)->toBeArray();
});

test('InterpreterNeuronAgent falls back to heuristic when not configured', function () {
    Config::set('neuron-ai.use_llm', true);
    Config::set('neuron-ai.providers.anthropic.key', null);

    $state = new IncidentState('El POS falla en cobrar.');
    $agent = app(InterpreterNeuronAgent::class);

    $result = $agent->handle($state);

    expect($result->summary)->not->toBeEmpty()
        ->and($result->intent)->not->toBeNull();
});

test('ClassifierNeuronAgent falls back to heuristic when LLM disabled', function () {
    Config::set('neuron-ai.use_llm', false);

    $state = new IncidentState('El POS falla.');
    $state->summary = 'POS error';
    $state->intent = 'report_issue';
    $agent = app(ClassifierNeuronAgent::class);

    $result = $agent->handle($state);

    expect($result->type)->not->toBeNull()
        ->and($result->area)->not->toBeNull()
        ->and($result->devRelated)->not->toBeNull();
});

test('Neuron agents log errors and fallback on LLM failure', function () {
    Config::set('neuron-ai.use_llm', true);
    Config::set('neuron-ai.providers.anthropic.key', 'invalid-key');
    Config::set('neuron-ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022');

    $state = new IncidentState('Test ticket');
    $agent = app(InterpreterNeuronAgent::class);

    // Should fallback to heuristic even if configured but fails
    $result = $agent->handle($state);

    expect($result->summary)->not->toBeEmpty();
});
