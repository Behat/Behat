<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class DecimalPointAnnotations implements Context
{
    /**
     * @Then :token should have value of £:value
     */
    public function shouldHaveValueOf($token, $value): void
    {
        echo $value;
    }
}
