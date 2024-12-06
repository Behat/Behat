<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

use Behat\Config\ConfigInterface;

interface FormatterConfigInterface extends ConfigInterface
{
    public function name(): string;

    public function isEnabled(): bool;
}
