<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\When;
use PHPUnit\Framework\Assert;

class EmptyParameterAttributes implements Context
{
    #[When('I enter the string :input')]
    public function multipleWrongNamedParameters($input): void
    {
        Assert::assertEquals('', $input);
    }
}
