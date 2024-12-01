<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class MultipleFeaturesContext implements Context
{
    private $value;

    #[Given('/I have entered (\d+)/')]
    public function iHaveEntered($num) {
        $this->value = $num;
    }

    #[Then('/I must have (\d+)/')]
    public function iMustHave($num) {
        PHPUnit\Framework\Assert::assertEquals($num, $this->value);
    }

    #[When('/I add (\d+)/')]
    public function iAdd($num) {
        $this->value += $num;
    }
}
