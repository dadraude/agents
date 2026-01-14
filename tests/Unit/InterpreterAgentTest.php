<?php

use App\AI\Agents\InterpreterAgent;
use App\AI\Orchestrator\IncidentState;

test('generates summary intent and entities', function () {
    $text = str_repeat('A', 200).' POS i KDS tenen un error intermitent.';
    $state = new IncidentState($text);

    $result = app(InterpreterAgent::class)->handle($state);

    expect($result->summary)->toHaveLength(160)
        ->and($result->intent)->toBe('report_issue')
        ->and($result->entities)->toContain('pos', 'kds')
        ->and($result->trace)->not->toBeEmpty();
});
