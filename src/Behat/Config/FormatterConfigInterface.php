<?php

declare(strict_types=1);

namespace Behat\Config;

interface FormatterConfigInterface extends ConfigInterface
{
    public function name(): string;
}
