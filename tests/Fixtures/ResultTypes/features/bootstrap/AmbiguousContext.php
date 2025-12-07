<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\Then;

class AmbiguousContext implements Context
{
    #[Given('/^human have chosen "([^"]*)"$/')]
    public function chosen($arg1): void
    {
        throw new PendingException();
    }

    #[Given('/^human have chosen "Latte"$/')]
    public function chosenLatte(): void
    {
        throw new PendingException();
    }

    #[Then('/^I should make him "([^"]*)"$/')]
    public function iShouldSee($money): void
    {
        throw new PendingException();
    }
}
