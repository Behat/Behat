<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

class WholeTableAnnotationsContext implements Context
{
    private array $data;

    /** @Transform table:* */
    public function transformTable(TableNode $table): array
    {
        return $table->getHash();
    }

    /** @Given data: */
    public function givenData(array $data): void
    {
        $this->data = $data;
    }

    /** @Then the :field should be :value */
    public function theFieldShouldBe(string $field, string $value): void
    {
        Assert::assertSame($value, $this->data[0][$field]);
    }
}
