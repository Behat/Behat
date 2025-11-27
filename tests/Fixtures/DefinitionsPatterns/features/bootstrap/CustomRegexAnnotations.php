<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class CustomRegexAnnotations implements Context
{
    /**
     * @Given /^I have entered (\d+)$/
     */
    public function iHaveEntered($number): void
    {
    }
}
