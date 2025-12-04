<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;

class FeatureContextMultiline implements Context
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

    #[When('/I (add|subtract) the value (\\d+)/')]
    public function iAddOrSubtract(string $op, string $num): void
    {
        if ($op === 'add') {
            $this->value += (int) $num;
        } elseif ($op === 'subtract') {
            $this->value -= (int) $num;
        }
    }
}
