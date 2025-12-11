<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\When;

class PendingContext implements Context
{
    #[Given('/^human have ordered very very very hot "([^"]*)"$/')]
    public function humanOrdered($arg1): void
    {
        throw new PendingException();
    }

    #[When('the coffee will be ready')]
    public function theCoffeeWillBeReady(): void
    {
        throw new PendingException();
    }
}
