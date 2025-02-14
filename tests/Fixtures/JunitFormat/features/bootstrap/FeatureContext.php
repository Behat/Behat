<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class FeatureContext implements Context
{
    private $value;

    #[Given('/I have entered (\d+)/')]
    public function iHaveEntered($num)
    {
        $this->value = $num;
    }

    #[When('/I add (\d+)/')]
    public function iAdd($num)
    {
        $this->value += $num;
    }

    #[When('/^Something not done yet$/')]
    public function somethingNotDoneYet()
    {
        throw new PendingException();
    }

    #[Then('/I must have (\d+)/')]
    public function iMustHave($num)
    {
        PHPUnit\Framework\Assert::assertEquals($num, $this->value);
    }

    #[BeforeScenario('@setup-error')]
    public function setup()
    {
        throw new Exception('This scenario has a failed setup');
    }

    #[When('/^I have a PHP error$/')]
    public function iHaveAPHPError()
    {
        $foo = new class () extends Context {};
    }
}
