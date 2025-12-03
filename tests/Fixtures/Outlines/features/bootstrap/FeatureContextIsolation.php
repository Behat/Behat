<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;

class FeatureContextIsolation implements Context
{
    private $number = 0;

    #[When('I add :number')]
    public function iAdd($number): void
    {
        $this->number += intval($number);
    }

    #[Then('the result should be :result')]
    public function theResultShouldBe($result): void
    {
        PHPUnit\Framework\Assert::assertEquals(intval($result), $this->number);
    }
}
