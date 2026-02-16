<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Transformation\Transform;
use PHPUnit\Framework\Assert;

class OrdinalTransformationAttributesContext implements Context
{
    private int $index;

    #[Transform('/^(0|[1-9]\d*)(?:st|nd|rd|th)?$/')]
    public function castToInt(string $number): int
    {
        return intval($number);
    }

    #[Given('I pick the :index thing')]
    public function iPickThing(int $index): void
    {
        $this->index = $index;
    }

    #[Then('the index should be :value')]
    public function theIndexShouldBe($value): void
    {
        Assert::assertSame($value, $this->index);
    }
}
