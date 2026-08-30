<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Behat\Tests\Fixtures\Assert;

class FeatureContextMinimalImports implements Context
{
    private $apples = 0;
    private $parameters;

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    #[Given('/^I have (\\d+) apples?$/')]
    public function iHaveApples($count)
    {
        $this->apples = intval($count);
    }

    #[When('/^I ate (\\d+) apples?$/')]
    public function iAteApples($count)
    {
        $this->apples -= intval($count);
    }

    #[When('/^I found (\\d+) apples?$/')]
    public function iFoundApples($count)
    {
        $this->apples += intval($count);
    }

    #[Then('/^I should have (\\d+) apples$/')]
    public function iShouldHaveApples($count)
    {
        Assert::assertEquals(intval($count), $this->apples);
    }

    #[Then('/^context parameter "([^"]*)" should be equal to "([^"]*)"$/')]
    public function contextParameterShouldBeEqualTo($key, $val)
    {
        Assert::assertEquals($val, $this->parameters[$key]);
    }

    #[Given('/^context parameter "([^"]*)" should be array with (\\d+) elements$/')]
    public function contextParameterShouldBeArrayWithElements($key, $count)
    {
        Assert::assertIsArray($this->parameters[$key]);
        Assert::assertEquals(2, count($this->parameters[$key]));
    }

    private function doSomethingUndefinedWith()
    {
    }
}
