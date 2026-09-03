<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Hook\AfterSuite;

class AfterSuiteContext implements Context
{
    #[AfterSuite]
    public static function failAfterSuite(): void
    {
        throw new RuntimeException('after suite hook failure');
    }
}
