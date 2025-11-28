<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use PHPUnit\Framework\Assert;

final class FeatureContext implements Context
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    #[Then('/the value should be configured as "([^"]+)"/')]
    public function theValueShouldBeConfiguredAs(string $expected): void
    {
        Assert::assertEquals($expected, $this->value);
    }
}
