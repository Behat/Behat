<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

class EmptyParameterAnnotations implements Context
{
    /**
     * @When I enter the string :input
     */
    public function multipleWrongNamedParameters($input): void
    {
        Assert::assertEquals('', $input);
    }
}
