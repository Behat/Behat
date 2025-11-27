<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class BrokenRegexAnnotations implements Context
{
    /**
     * @Then /I am (foo/
     */
    public function invalidRegex(): void
    {
    }
}
