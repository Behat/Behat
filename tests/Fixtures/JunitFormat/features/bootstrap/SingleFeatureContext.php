<?php

use Behat\Behat\Context\Context,
    Behat\Behat\Tester\Exception\PendingException;

class SingleFeatureContext implements Context
{
    private $value;

    /**
     * @Given /I have entered (\d+)/
     */
    public function iHaveEntered($num) {
        $this->value = $num;
    }

    /**
     * @Then /I must have (\d+)/
     */
    public function iMustHave($num) {
        PHPUnit\Framework\Assert::assertEquals($num, $this->value);
    }

    /**
     * @When /I add (\d+)/
     */
    public function iAdd($num) {
        $this->value += $num;
    }

    /**
     * @When /^Something not done yet$/
     */
    public function somethingNotDoneYet() {
        throw new PendingException();
    }
}
