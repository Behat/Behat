<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class FeatureContext implements Context
{
    #[Given('/^Some slow step N(\d+)$/')]
    public function someSlowStepN(string $num): void
    {
    }

    #[Given('/^Some normal step N(\d+)$/')]
    public function someNormalStepN(string $num): void
    {
    }

    #[Given('/^Some fast step N(\d+)$/')]
    public function someFastStepN(string $num): void
    {
    }
}
