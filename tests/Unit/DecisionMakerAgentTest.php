<?php

use App\AI\Agents\DecisionMakerAgent;
use App\AI\Orchestrator\IncidentState;

test('escalates high priority bug or feature', function () {
    $state = new IncidentState('Crash al fer pagar.');
    $state->type = 'bug';
    $state->priorityScore = 4.25;

    $result = app(DecisionMakerAgent::class)->handle($state);

    expect($result->shouldEscalate)->toBeTrue()
        ->and($result->decisionReason)->toBe('High priority bug/feature based on impact/urgency/severity.');
});

test('escalates when user is blocked even with low priority', function () {
    $state = new IncidentState('No podem cobrar amb el POS.');
    $state->type = 'question';
    $state->priorityScore = 1.5;

    $result = app(DecisionMakerAgent::class)->handle($state);

    expect($result->shouldEscalate)->toBeTrue()
        ->and($result->decisionReason)->toBe('User is blocked (payment/operations).');
});

test('does not escalate by default', function () {
    $state = new IncidentState('Com puc canviar el logo?');
    $state->type = 'question';
    $state->priorityScore = 2.0;

    $result = app(DecisionMakerAgent::class)->handle($state);

    expect($result->shouldEscalate)->toBeFalse()
        ->and($result->decisionReason)->toBe('Not escalated by default.');
});
