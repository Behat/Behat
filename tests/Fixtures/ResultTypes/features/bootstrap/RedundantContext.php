<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class RedundantContext implements Context
{
    #[Given('/^customer bought coffee$/')]
    public function chosen($arg1): void
    {
        // do something
    }

    #[Given('/^customer bought coffee$/')]
    public function chosenLatte(): void
    {
        // do something else
    }
}
