<?php

declare(strict_types=1);

use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;

trait ApplesDefinitions
{
    private int $apples = 0;

    #[Given('/^I have (\\d+) apples?$/')]
    public function iHaveApples($count): void
    {
        $this->apples = (int) $count;
    }

    #[When('/^I ate (\\d+) apples?$/')]
    public function iAteApples($count): void
    {
        $this->apples -= (int) $count;
    }

    #[When('/^I found (\\d+) apples?$/')]
    public function iFoundApples($count): void
    {
        $this->apples += (int) $count;
    }

    #[Then('/^I should have (\\d+) apples$/')]
    public function iShouldHaveApples($count): void
    {
        Assert::assertEquals((int) $count, $this->apples);
    }
}
