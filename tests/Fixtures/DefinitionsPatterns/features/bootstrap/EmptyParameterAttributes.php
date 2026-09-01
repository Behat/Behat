<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\When;
use Behat\Tests\Fixtures\Assert;

class EmptyParameterAttributes implements Context
{
    #[When('I enter the string :input')]
    public function multipleWrongNamedParameters($input): void
    {
        Assert::assertEquals('', $input);
    }
}
