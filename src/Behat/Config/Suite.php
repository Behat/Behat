<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Filter\FilterInterface;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;

final class Suite
{
    public function __construct(
        private string $name,
        private array $settings = [],
    ) {
    }

    public function withContexts(string ...$contexts): self
    {
        foreach ($contexts as $context) {
            $this->settings['contexts'][] = $context;
        }

        return $this;
    }

    /**
     * @param array<mixed> $constructorArgs
     */

    public function addContext(string $context, array $constructorArgs = []): self
    {
        $this->settings['contexts'][][$context] = $constructorArgs;

        return $this;
    }

    public function withPaths(string ...$paths): self
    {
        foreach ($paths as $path) {
            $this->settings['paths'][] = $path;
        }

        return $this;
    }

    public function withFilter(FilterInterface $filter): self
    {
        if (array_key_exists($filter->name(), $this->settings['filters'] ?? [])) {
            throw new ConfigurationLoadingException(sprintf('The filter "%s" already exists.', $filter->name()));
        }

        $this->settings['filters'][$filter->name()] = $filter->value();

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return $this->settings;
    }
}
