<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class DescriptionsContext implements Context
{
    #[Given('/^I have (\\d+) apples?$/')]
    public function iHaveApples($count): void
    {
        throw new PendingException();
    }

    /**
     * Eating apples.
     *
     * More details on eating apples, and a list:
     * - one
     * - two
     * --
     * Internal note not showing in help.
     */
    #[When('/^I ate (\\d+) apples?$/')]
    public function iAteApples($count): void
    {
        throw new PendingException();
    }

    #[When('/^I found (\\d+) apples?$/')]
    public function iFoundApples($count): void
    {
        throw new PendingException();
    }

    #[Then('/^I should have (\\d+) apples$/')]
    public function iShouldHaveApples($count): void
    {
        throw new PendingException();
    }
}
