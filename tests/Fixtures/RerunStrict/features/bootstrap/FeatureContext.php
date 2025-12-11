<?php

use Behat\Behat\Context\Context;
use Behat\Step\When;
use Exception;

class FeatureContext implements Context
{
    #[When('I have a failing step')]
    public function iHaveAFailingStep(): void
    {
        throw new Exception();
    }

    #[When('I have a passing step')]
    public function iHaveAPassingStep(): void
    {
    }
}
