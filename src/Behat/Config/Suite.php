<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Filter\FilterInterface;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;

final class Suite
{
    private const CONTEXTS_SETTING = 'contexts';
    private const PATHS_SETTING = 'paths';
    private const FILTERS_SETTING = 'filters';

    public function __construct(
        private readonly string $name,
        private array $settings = [],
    ) {
    }

    public function withContexts(string ...$contexts): self
    {
        foreach ($contexts as $context) {
            $this->settings[self::CONTEXTS_SETTING][] = $context;
        }

        return $this;
    }

    /**
     * @param array<mixed> $constructorArgs
     */
    public function addContext(string $context, array $constructorArgs = []): self
    {
        $this->settings[self::CONTEXTS_SETTING][][$context] = $constructorArgs;

        return $this;
    }

    public function withPaths(string ...$paths): self
    {
        foreach ($paths as $path) {
            $this->settings[self::PATHS_SETTING][] = $path;
        }

        return $this;
    }

    public function withFilter(FilterInterface $filter): self
    {
        if (array_key_exists($filter->name(), $this->settings[self::FILTERS_SETTING] ?? [])) {
            throw new ConfigurationLoadingException(sprintf('The filter "%s" already exists.', $filter->name()));
        }

        $this->settings[self::FILTERS_SETTING][$filter->name()] = $filter->value();

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
