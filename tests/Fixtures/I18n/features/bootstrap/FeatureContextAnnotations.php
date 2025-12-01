<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;

class FeatureContextAnnotations implements Context
{
    private int $value = 0;

    /**
     * @Given /^Я ввел (\d+)$/
     */
    public function iHaveEntered(int $number): void
    {
        $this->value = $number;
    }

    /**
     * @When /^Я добавлю (\d+)$/
     */
    public function iAdd(int $number): void
    {
        $this->value += $number;
    }

    /**
     * @Then /^Я должен иметь (\d+)$/
     */
    public function iShouldHave(int $number): void
    {
        if ($this->value !== $number) {
            throw new Exception(sprintf('Failed asserting that %d matches expected %d.', $this->value, $number));
        }
    }

    /**
     * @Given /^Что-то еще не сделано$/
     */
    public function somethingNotDone(): void
    {
        throw new PendingException();
    }
}
