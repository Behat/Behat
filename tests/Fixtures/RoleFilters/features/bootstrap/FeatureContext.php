<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class FeatureContext implements Context
{
    #[Given('/^I have (\d+) apples?$/')]
    public function iHaveApples(int $count): void
    {
    }

    #[When('/^I ate (\d+) apples?$/')]
    public function iAteApples(int $count): void
    {
    }

    #[Then('/^I should have (\d+) apples?$/')]
    public function iShouldHaveApples(int $count): void
    {
    }
}
