<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Per defecte, tots els tests s'executen amb la IA desactivada
        // per evitar qualsevol trucada real a LLM. Els tests que necessitin
        // modificar aquest comportament han d'ajustar explícitament la config.
        config(['neuron-ai.use_llm' => false]);
    }
}
