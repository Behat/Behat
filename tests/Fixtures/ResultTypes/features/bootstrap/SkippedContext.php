<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;

class SkippedContext implements Context
{
    private $money = 0;

    #[Given('/^human bought coffee$/')]
    public function humanBoughtCoffee(): void
    {
    }

    #[Given('/^I have water$/')]
    public function water(): void
    {
    }

    #[Given('/^I have no water$/')]
    public function noWater(): void
    {
        throw new Exception('NO water in coffee machine!!!');
    }

    #[Given('/^I have electricity$/')]
    public function haveElectricity(): void
    {
    }

    #[Given('/^I have no electricity$/')]
    public function haveNoElectricity(): void
    {
        throw new Exception('NO electricity in coffee machine!!!');
    }

    #[When('/^I boil water$/')]
    public function boilWater(): void
    {
    }

    #[Then('/^the coffee should be almost done$/')]
    public function coffeeAlmostDone(): void
    {
    }

    #[Then('/^I should see (\d+)\$ on the screen$/')]
    public function iShouldSee($money): void
    {
        Assert::assertEquals($money, $this->money);
    }
}
