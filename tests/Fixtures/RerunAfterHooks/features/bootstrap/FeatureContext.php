<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Hook\AfterScenario;
use Behat\Step\Given;

class FeatureContext implements Context
{
    #[Given('a step that passes')]
    public function aStepThatPasses(): void
    {
    }

    #[AfterScenario('@failing-after-scenario-hook')]
    public static function failAfterScenario(): void
    {
        throw new RuntimeException('after scenario hook failure');
    }
}
