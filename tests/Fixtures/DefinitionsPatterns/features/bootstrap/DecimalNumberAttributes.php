<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Tests\Fixtures\Assert;

class DecimalNumberAttributes implements Context
{
    #[Given('I have a package v:version')]
    public function multipleWrongNamedParameters($version): void
    {
        Assert::assertEquals('2.5', $version);
    }
}
