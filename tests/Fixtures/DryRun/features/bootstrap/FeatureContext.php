<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

final class FeatureContext implements Context
{
    #[BeforeScenario]
    public static function beforeScenario(): void
    {
        echo 'HOOK: before scenario';
    }

    #[Given('/^I have (\\d+) apples?$/')]
    public function iHaveApples(int $count): void
    {
        echo "STEP: I have $count apples";
    }

    #[When('/^I ate (\\d+) apples?$/')]
    public function iAteApples(int $count): void
    {
        echo "STEP: I ate $count apples";
    }

    #[When('/^I found (\\d+) apples?$/')]
    public function iFoundApples(int $count): void
    {
        echo "STEP: I found $count apples";
    }

    #[Then('/^I should have (\\d+) apples$/')]
    public function iShouldHaveApples(int $count): void
    {
        echo "STEP: I should have $count apples";
    }
}
