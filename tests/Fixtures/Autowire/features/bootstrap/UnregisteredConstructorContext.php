<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class UnregisteredConstructorContext implements Context
{
    public function __construct(Service4 $s)
    {
    }

    #[Given('a step')]
    public function aStep()
    {
    }
}
