<?php

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;

class FeatureContext implements Context
{
    #[When('a passing step')]
    public function passingStep(): void
    {
    }

    #[When('a failing step')]
    public function failingStep(): void
    {
        throw new Exception('step failed as supposed');
    }

    #[Then('a skipped step')]
    public function skippedStep(): void
    {
    }
}
