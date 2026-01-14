<?php

use App\AI\Agents\PrioritizerAgent;
use App\AI\Orchestrator\IncidentState;

test('sets highest priority when operations are blocked', function () {
    $state = new IncidentState('No podem cobrar, estem blocked.');
    $state->type = 'bug';

    $result = app(PrioritizerAgent::class)->handle($state);

    expect($result->impact)->toBe(5)
        ->and($result->urgency)->toBe(5)
        ->and($result->severity)->toBe(5)
        ->and($result->priorityScore)->toBe(5.0);
});

test('reduces urgency and severity for questions', function () {
    $state = new IncidentState('Com puc activar el KDS?');
    $state->type = 'question';

    $result = app(PrioritizerAgent::class)->handle($state);

    expect($result->impact)->toBe(3)
        ->and($result->urgency)->toBe(2)
        ->and($result->severity)->toBe(2)
        ->and($result->priorityScore)->toBe(2.4);
});
