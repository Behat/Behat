<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

final class FeatureContext implements Context
{
    private array $loadedExtensions = [];

    public function addExtension(string $extensionName): void
    {
        $this->loadedExtensions[] = $extensionName;
    }

    /** @When this scenario executes */
    public function thisScenarioExecutes(): void
    {
    }

    /** @Then the extension :name should be loaded */
    public function theExtensionLoaded(string $name): void
    {
        Assert::assertContains($name, $this->loadedExtensions);
    }

    /** @Then the extension :name should not be loaded */
    public function theExtensionNotLoaded(string $name): void
    {
        Assert::assertNotContains($name, $this->loadedExtensions);
    }
}
