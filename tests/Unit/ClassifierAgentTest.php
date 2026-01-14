<?php

use App\AI\Agents\ClassifierAgent;
use App\AI\Orchestrator\IncidentState;

test('classifies bug and area from raw text', function () {
    $state = new IncidentState('El POS falla i fa crash quan cobro.');

    $result = app(ClassifierAgent::class)->handle($state);

    expect($result->type)->toBe('bug')
        ->and($result->area)->toBe('pos')
        ->and($result->devRelated)->toBeTrue()
        ->and($result->trace)->not->toBeEmpty();
});

test('area detection prefers the last matching rule', function () {
    $state = new IncidentState('KDS i POS no responen.');

    $result = app(ClassifierAgent::class)->handle($state);

    expect($result->area)->toBe('pos');
});
