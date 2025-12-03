<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;

final class FeatureContext implements Context
{
    private int $apples = 0;

    private array $parameters;

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    #[Given('/^I have (\\d+) apples?$/')]
    public function iHaveApples(string $count): void
    {
        $this->apples = (int) $count;
    }

    #[When('/^I ate (\\d+) apples?$/')]
    public function iAteApples(string $count): void
    {
        $this->apples -= (int) $count;
    }

    #[When('/^I found (\\d+) apples?$/')]
    public function iFoundApples(string $count): void
    {
        $this->apples += (int) $count;
    }

    #[Then('/^I should have (\\d+) apples$/')]
    public function iShouldHaveApples(string $count): void
    {
        Assert::assertEquals((int) $count, $this->apples);
    }

    #[Then('/^context parameter "([^"]*)" should be equal to "([^"]*)"$/')]
    public function contextParameterShouldBeEqualTo(string $key, string $val): void
    {
        Assert::assertEquals($val, $this->parameters[$key]);
    }

    #[Given('/^context parameter "([^"]*)" should be array with (\\d+) elements$/')]
    public function contextParameterShouldBeArrayWithElements(string $key, string $count): void
    {
        Assert::assertIsArray($this->parameters[$key]);
        Assert::assertEquals(2, count($this->parameters[$key]));
    }
}
