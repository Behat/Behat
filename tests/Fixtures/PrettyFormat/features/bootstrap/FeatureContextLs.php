<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;

class FeatureContextLs implements Context
{
    private int $value = 10;

    #[Then('/I must have "([^"]+)"/')]
    public function iMustHave(string $num): void
    {
        PHPUnit\Framework\Assert::assertEquals(intval(preg_replace('/[^\d]+/', '', $num)), $this->value);
    }

    #[When('/I add "([^"]+)"/')]
    public function iAdd(string $num): void
    {
        $this->value += intval(preg_replace('/[^\d]+/', '', $num));
    }
}
