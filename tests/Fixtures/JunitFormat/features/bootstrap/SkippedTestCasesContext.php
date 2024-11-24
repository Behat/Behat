<?php

use Behat\Behat\Context\Context;

class SkippedTestCasesContext implements Context
{
    private $value;

    /**
     * @BeforeScenario
     */
    public function setup() {
        throw new \Exception();
    }

    /**
     * @Given /I have entered (\d+)/
     * @Then /^I must have (\d+)$/
     */
    public function action($num)
    {
    }
}
