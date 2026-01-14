<?php

use App\AI\Agents\ValidatorAgent;
use App\AI\Orchestrator\IncidentState;

test('flags missing info for bugs without steps or version', function () {
    $state = new IncidentState('El POS falla quan cobrem.');
    $state->type = 'bug';

    $result = app(ValidatorAgent::class)->handle($state);

    expect($result->isSufficient)->toBeFalse()
        ->and($result->missingInfo)->toContain('steps_to_reproduce', 'environment_version');
});

test('accepts bug reports with steps and environment version', function () {
    $state = new IncidentState('Error al POS. Passos: obrir, cobrar. iOS 17.2.');
    $state->type = 'bug';

    $result = app(ValidatorAgent::class)->handle($state);

    expect($result->isSufficient)->toBeTrue()
        ->and($result->missingInfo)->toBeEmpty();
});
