<?php

declare(strict_types=1);

namespace Behat\Config;

final class Config implements ConfigInterface
{
    public function __construct(
        private array $settings = []
    ) {
    }

    public function import(string $file): self
    {
        $this->settings['imports'][] = $file;

        return $this;
    }

    public function toArray(): array
    {
        return $this->settings;
    }
}
