<?php

declare(strict_types=1);

namespace Behat\Config;

interface ExtensionConfigInterface extends ConfigInterface
{
    public function name(): string;
}
