<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class MultilineTitlesContext implements Context
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

    #[When('/I (add|subtract) the value (\d+)/')]
    public function iAddOrSubstact($op, $num) {
        if ($op == 'add')
            $this->value += $num;
        elseif ($op == 'subtract')
            $this->value -= $num;
    }
}
