<?php

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Behat\Tests\Fixtures\Assert;

class StepDefinitionArgsContext implements Context
{
    #[When('I set the state to :value')]
    public function setState($value, Service2 $s2)
    {
        $s2->state = $value;
    }

    #[Then('that state should be persisted as :value')]
    public function checkState($val, Service2 $s2)
    {
        Assert::assertEquals($val, $s2->state);
    }
}
