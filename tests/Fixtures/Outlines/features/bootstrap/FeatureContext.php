<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class FeatureContext implements Context
{
    private $result;
    private $numbers;

    #[Given('/^I have basic calculator$/')]
    public function iHaveBasicCalculator(): void
    {
        $this->result = 0;
        $this->numbers = [];
    }

    #[Given('/^I have entered (\d+)$/')]
    public function iHaveEntered($number): void
    {
        $this->numbers[] = intval($number);
    }

    #[When('/^I add$/')]
    public function iAdd(): void
    {
        foreach ($this->numbers as $number) {
            $this->result += $number;
        }
        $this->numbers = [];
    }

    #[When('/^I sub$/')]
    public function iSub(): void
    {
        $this->result = array_shift($this->numbers);
        foreach ($this->numbers as $number) {
            $this->result -= $number;
        }
        $this->numbers = [];
    }

    #[When('/^I multiply$/')]
    public function iMultiply(): void
    {
        $this->result = array_shift($this->numbers);
        foreach ($this->numbers as $number) {
            $this->result *= $number;
        }
        $this->numbers = [];
    }

    #[When('/^I div$/')]
    public function iDiv(): void
    {
        $this->result = array_shift($this->numbers);
        foreach ($this->numbers as $number) {
            $this->result /= $number;
        }
        $this->numbers = [];
    }

    #[Then('/^The result should be (\d+)$/')]
    public function theResultShouldBe($result): void
    {
        PHPUnit\Framework\Assert::assertEquals(intval($result), $this->result);
    }
}
