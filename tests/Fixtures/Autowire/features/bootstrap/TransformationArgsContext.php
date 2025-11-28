<?php

use Behat\Behat\Context\Context;

class TransformationArgsContext implements Context
{
    /** @Transform :flag */
    public function fromFlag($flag, Service2 $s2)
    {
        return $s2->$flag;
    }

    /** @When I set the :flat flag to :value */
    public function setState($flag, $value, Service2 $s2)
    {
        $s2->$flag = $value;
    }

    /** @Then the :flag flag should be persisted as :value */
    public function checkState($flag, $value)
    {
        PHPUnit\Framework\Assert::assertEquals($value, $flag);
    }
}
