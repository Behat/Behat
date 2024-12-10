<?php

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;

class SkippedTestCasesContext implements Context
{
    private $value;

    #[BeforeScenario]
    public function setup() {
        throw new \Exception();
    }

    #[Given('/I have entered (\d+)/')]
    #[Then('/^I must have (\d+)$/')]
    public function action($num)
    {
    }
}
