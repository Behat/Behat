<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;

class FeatureContextWithHook implements Context
{
    #[BeforeScenario]
    public function beforeScenario(): void
    {
        echo 'Setting up';
    }

    #[Given('step')]
    public function step(): void
    {
    }
}
