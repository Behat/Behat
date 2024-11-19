<?php

declare(strict_types=1);

namespace Behat\Config;

final class Config
{
    public function __construct(
        private array $settings = []
    ) {
    }

    public function toArray(): array
    {
        return $this->settings;
    }
}
