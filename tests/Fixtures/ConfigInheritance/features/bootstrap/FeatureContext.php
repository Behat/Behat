<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;

class FeatureContext implements Context
{
    private array $parameters;
    private array $extension;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function setExtensionParameters(array $parameters): void
    {
        $this->extension = $parameters;
    }

    #[When('this scenario executes')]
    public function thisScenarioExecutes(): void
    {
    }

    #[Then('the context parameters should be overwritten')]
    public function theContextParametersOverwrite(): void
    {
        Assert::assertEquals(['param2' => 'val2'], $this->parameters);
    }

    #[Then('the extension config should be merged')]
    public function theExtensionConfigMerge(): void
    {
        Assert::assertEquals(['param1' => 'val2', 'param2' => 'val1'], $this->extension);
    }
}
