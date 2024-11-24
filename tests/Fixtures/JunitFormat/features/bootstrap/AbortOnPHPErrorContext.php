<?php
use Behat\Behat\Context\Context;
class Foo {}
class AbortOnPHPErrorContext implements Context
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
        $foo = new class extends Foo implements Foo {};
    }
    /**
     * @When /I add (\d+)/
     */
    public function iAdd($num) {
        $this->value += $num;
    }
}
