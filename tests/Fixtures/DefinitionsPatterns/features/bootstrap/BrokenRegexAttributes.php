<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;

class BrokenRegexAttributes implements Context
{
    #[Then('/I am (foo/')]
    public function invalidRegex(): void
    {
    }
}
