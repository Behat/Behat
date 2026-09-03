<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Behat\Tests\Fixtures\Assert;

class FeatureContext implements Context
{
    private int $offset = 0;
    private int $result;

    #[Given('some setup has happened')]
    public function someSetupHasHappened(): void
    {
    }

    #[When('I add :arg1 + :arg2')]
    public function iAdd(int $arg1, int $arg2): void
    {
        $this->result = $arg1 + $arg2 + $this->offset;
    }

    #[Then('the result should be :arg1')]
    public function theResultShouldBe(int $arg1): void
    {
        Assert::assertSame($this->result, $arg1);
    }

    #[When('I divide :arg1 by :arg2')]
    public function iDivideBy(int $arg1, int $arg2): void
    {
        $this->result = ((int) $arg1 / $arg2) + $this->offset;
    }

    #[Given('the calculator has a fixed offset of :arg1')]
    public function theCalculatorHasAFixedOffsetOf(int $arg1): void
    {
        $this->offset = $arg1;
    }
}
