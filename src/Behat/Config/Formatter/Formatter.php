<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

class Formatter implements FormatterConfigInterface
{
    private bool $enabled = true;

    public function __construct(
        private readonly string $name,
        private array $settings = [],
    ) {
    }

    public function enable(): self
    {
        $this->enabled = true;

        return $this;
    }

    public function disable(): self
    {
        $this->enabled = false;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function toArray(): array
    {
        return $this->settings;
    }
}
