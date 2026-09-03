<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class FeatureContext implements Context
{
    public function __construct(
        private bool $shouldFail = false,
    ) {
    }

    #[Given('a step that passes')]
    public function aStepThatPasses(): void
    {
    }

    #[Given('a step that only fails in the "failing" suite')]
    public function aStepThatOnlyFailsInOneSuite(): void
    {
        if ($this->shouldFail) {
            throw new RuntimeException('step failure');
        }
    }
}
