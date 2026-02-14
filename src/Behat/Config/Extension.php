<?php

declare(strict_types=1);

namespace Behat\Config;

final class Extension implements ExtensionConfigInterface
{
    public function __construct(
        private readonly string $name,
        private readonly array $settings = [],
    ) {
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
