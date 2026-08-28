<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;

class ArgumentTransformerContext implements Context
{
    #[Then('the argument is :value')]
    public function theArgumentIs(string $value): void
    {
        echo $value;
    }
}
