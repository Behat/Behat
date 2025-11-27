<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class CustomRegexAttributes implements Context
{
    #[Given('/^I have entered (\\d+)$/')]
    public function iHaveEntered($number): void
    {
    }
}
