<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;

class FeatureContext implements Context
{
    private int $value;

    #[Given('/I have entered (\\d+)/')]
    public function iHaveEntered(string $num): void
    {
        $this->value = (int) $num;
    }

    #[Then('/I must have (\\d+)/')]
    public function iMustHave(string $num): void
    {
        Assert::assertEquals($num, $this->value);
    }

    #[When('/I add (\\d+)/')]
    public function iAdd(string $num): void
    {
        $this->value += (int) $num;
    }

    #[When('/^Something not done yet$/')]
    public function somethingNotDoneYet(): void
    {
        throw new PendingException();
    }
}
