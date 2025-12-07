<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class WarningContext implements Context
{
    #[Given('/^customer bought coffee$/')]
    public function chosen(): void
    {
        trigger_error('some warning', E_USER_WARNING);
    }

    #[Given('/^customer bought another one coffee$/')]
    public function chosenLatte(): void
    {
        // do something else
    }
}
