<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class FeatureContext implements Context
{
    #[Given('I have a passing step')]
    public function iHaveAPassingStep()
    {
    }

    #[Given('I have a step that throws an exception')]
    public function iHaveAFailingStep()
    {
        $a = $b;
    }
}
