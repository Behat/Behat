<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;

class StringWithPointAttributes implements Context
{
    #[Then(':token should have value of :first.:second')]
    public function shouldHaveValueOf($token, $first, $second): void
    {
        echo $first . ' + ' . $second;
    }
}
