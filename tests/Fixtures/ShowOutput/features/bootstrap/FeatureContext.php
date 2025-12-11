<?php

use Behat\Behat\Context\Context;
use Behat\Step\When;
use Exception;

class FeatureContext implements Context
{
    #[When('I have a step that has no output and passes')]
    public function passingWithoutOutput(): void
    {
    }

    #[When('I have a step that shows some output and passes')]
    public function passingWithOutput(): void
    {
        echo 'This step has some output';
    }

    #[When('I have a step that shows some output and fails')]
    public function failingWithOutput(): void
    {
        echo 'This step also has output';
        throw new Exception('step failed as supposed');
    }
}
