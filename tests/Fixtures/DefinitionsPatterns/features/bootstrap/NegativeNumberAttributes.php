<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use PHPUnit\Framework\Assert;

class NegativeNumberAttributes implements Context
{
    #[Given('I have a negative number :num')]
    public function multipleWrongNamedParameters($num): void
    {
        Assert::assertEquals('-3', $num);
    }
}
