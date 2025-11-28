<?php

use Behat\Behat\Context\Context;

class UnregisteredStepContext implements Context
{
    /** @Given a step */
    public function aStep(Service4 $s)
    {
    }
}
