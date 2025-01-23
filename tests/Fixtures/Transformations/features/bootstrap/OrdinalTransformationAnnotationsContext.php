<?php

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

class OrdinalTransformationAnnotationsContext implements Context
{
    private int $index;

    /** @Transform /^(0|[1-9]\d*)(?:st|nd|rd|th)?$/ */
    public function castToInt(string $number): int {
        return intval($number);
    }

    /** @Given I pick the :index thing */
    public function iPickThing(int $index): void {
        $this->index = $index;
    }

    /** @Then the index should be :value */
    public function theIndexShouldBe($value): void {
        Assert::assertSame($value, $this->index);
    }
}
