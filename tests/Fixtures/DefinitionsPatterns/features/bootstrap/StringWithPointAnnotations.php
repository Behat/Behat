<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class StringWithPointAnnotations implements Context
{
    /**
     * @Then :token should have value of :first.:second
     */
    public function shouldHaveValueOf($token, $first, $second): void
    {
        echo $first . ' + ' . $second;
    }
}
