<?php

declare(strict_types=1);

namespace Behat\Config;

final class Formatter implements FormatterConfigInterface
{
    public function __construct(
        private readonly string $name,
        private array $settings = [],
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
