<?php

declare(strict_types=1);

namespace Behat\Config;

final class Suite
{
    public function __construct(
        private string $name,
        private array $settings = [],
        private string $profile = 'default',
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

    public function name(): string
    {
        return $this->name;
    }

    public function profile(): ?string
    {
        return $this->profile;
    }

    public function toArray(): array
    {
        return $this->settings;
    }
}
