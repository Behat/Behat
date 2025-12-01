<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;

final class FeatureContext implements Context
{
    private array $extension;

    public function setExtensionParameters(array $parameters): void
    {
        $this->extension = $parameters;
    }

    #[When('this scenario executes')]
    public function thisScenarioExecutes(): void
    {
    }

    #[Then('the extension should be loaded')]
    public function theExtensionLoaded(): void
    {
        PHPUnit\Framework\Assert::assertEquals(['param1' => 'val1', 'param2' => 'val2'], $this->extension);
    }
}
