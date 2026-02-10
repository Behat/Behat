<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

final class FeatureContext implements Context
{
    private int $number = 0;
    private string $timeOfDay = '';

    #[Given('/^I have the number (\d+)$/')]
    public function iHaveTheNumber(int $number): void
    {
        $this->number = $number;
    }

    #[When('/^I add (\d+)$/')]
    public function iAdd(int $value): void
    {
        $this->number += $value;
    }

    #[When('/^I subtract (\d+)$/')]
    public function iSubtract(int $value): void
    {
        $this->number -= $value;
    }

    #[When('/^I multiply by (\d+)$/')]
    public function iMultiplyBy(int $value): void
    {
        $this->number *= $value;
    }

    #[Then('/^the result should be (\d+)$/')]
    public function theResultShouldBe(int $expected): void
    {
        if ($this->number !== $expected) {
            throw new RuntimeException(sprintf('Expected %d but got %d', $expected, $this->number));
        }
    }

    #[Given('/^the time is (morning|afternoon|evening)$/')]
    public function theTimeIs(string $timeOfDay): void
    {
        $this->timeOfDay = $timeOfDay;
    }

    #[Then('/^I should see "([^"]+)"$/')]
    public function iShouldSee(string $message): void
    {
        $expected = match ($this->timeOfDay) {
            'morning' => 'Good morning',
            'afternoon' => 'Good afternoon',
            'evening' => 'Good evening',
            default => '',
        };
        if ($message !== $expected) {
            throw new RuntimeException(sprintf('Expected "%s" but got "%s"', $expected, $message));
        }
    }
}
