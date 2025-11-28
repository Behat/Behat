<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

class UnixPathAnnotations implements Context
{
    /**
     * @Then images should be uploaded to web\/uploads\/media\/default\/:arg1\/:arg2\/
     */
    public function multipleWrongNamedParameters($arg1, $arg2): void
    {
        Assert::assertEquals('0001', $arg1);
        Assert::assertEquals('01', $arg2);
    }
}
