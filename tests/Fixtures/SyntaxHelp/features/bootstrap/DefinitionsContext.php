<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class DefinitionsContext implements Context
{
    #[Given('/^(?:I|We) have (\\d+) apples?$/')]
    public function iHaveApples($count): void
    {
        throw new PendingException();
    }

    #[When('/^(?:I|We) ate (\\d+) apples?$/')]
    public function iAteApples($count): void
    {
        throw new PendingException();
    }

    #[When('/^(?:I|We) found (\\d+) apples?$/')]
    public function iFoundApples($count): void
    {
        throw new PendingException();
    }

    #[Then('/^(?:I|We) should have (\\d+) apples$/')]
    public function iShouldHaveApples($count): void
    {
        throw new PendingException();
    }
}
