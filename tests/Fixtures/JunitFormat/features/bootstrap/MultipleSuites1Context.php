<?php

use Behat\Behat\Context\Context;

class MultipleSuites1Context implements Context
{
    protected $strongLevel;

    /**
     * @Given I am not strong
     */
    public function iAmNotStrong() {
        $this->strongLevel = 0;
    }

    /**
     * @When /I eat an apple/
     */
    public function iEatAnApple() {
        $this->strongLevel += 2;
    }

    /**
     * @Then /I will be stronger/
     */
    public function iWillBeStronger() {
        PHPUnit\Framework\Assert::assertNotEquals(0, $this->strongLevel);
    }
}
