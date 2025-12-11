<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use PHPUnit\Framework\Assert;

class FailedContext implements Context
{
    private $money = 0;

    #[Given('/^I have thrown (\d+)\$ into machine$/')]
    public function pay($money): void
    {
        $this->money += $money;
    }

    #[Then('/^I should see (\d+)\$ on the screen$/')]
    public function iShouldSee($money): void
    {
        Assert::assertEquals($money, $this->money);
    }
}
