<?php

declare(strict_types=1);

namespace Behat\Config;

use function is_string;

final class Config implements ConfigInterface
{
    public function __construct(
        private array $settings = []
    ) {
    }

    public function import(string|array $resource): self
    {
        $resources = is_string($resource) ? [$resource] : $resource;

        foreach ($resources as $resource) {
            $this->settings['imports'][] = $resource;
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->settings;
    }
}
