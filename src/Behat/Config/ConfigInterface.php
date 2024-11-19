<?php

declare(strict_types=1);

namespace Behat\Config;

interface ConfigInterface
{
    public function toArray(): array;
}
