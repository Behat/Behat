<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\When;

final class FeatureContext implements Context
{
    #[When('this scenario executes')]
    public function thisScenarioExecutes(): void
    {
    }
}
