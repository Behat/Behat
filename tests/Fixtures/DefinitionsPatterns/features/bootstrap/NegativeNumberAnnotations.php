<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

class NegativeNumberAnnotations implements Context
{
    /**
     * @Given I have a negative number :num
     */
    public function multipleWrongNamedParameters($num): void
    {
        Assert::assertEquals('-3', $num);
    }
}
