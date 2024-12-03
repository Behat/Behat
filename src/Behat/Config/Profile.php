<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Gherkin\Filter\FilterInterface;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;

final class Profile
{
    public function __construct(
        private string $name,
        private array $settings = []
    ) {
    }

    public function withSuite(Suite $suite): self
    {
        if (array_key_exists($suite->name(), $this->settings['suites'] ?? [])) {
            throw new ConfigurationLoadingException(sprintf('The suite "%s" already exists.', $suite->name()));
        }

        $this->settings['suites'][$suite->name()] = $suite->toArray();

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function withExtension(ExtensionConfigInterface $extension): self
    {
        if (array_key_exists($extension->name(), $this->settings['extensions'] ?? [])) {
            throw new ConfigurationLoadingException(sprintf('The extension "%s" already exists.', $extension->name()));
        }

        $this->settings['extensions'][$extension->name()] = $extension->toArray();

        return $this;
    }

    public function withFilter(FilterInterface $filter): self
    {
        if (array_key_exists($filter->name(), $this->settings['gherkin']['filters'] ?? [])) {
            throw new ConfigurationLoadingException(sprintf('The filter "%s" already exists.', $filter->name()));
        }

        $this->settings['gherkin']['filters'][$filter->name()] = $filter->value();

        return $this;
    }

    public function toArray(): array
    {
        return $this->settings;
    }
}
