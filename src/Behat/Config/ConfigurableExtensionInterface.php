<?php

declare(strict_types=1);

namespace Behat\Config;

interface ConfigurableExtensionInterface
{
    public function getExtensionConfigObject(string $name, array $settings): Extension;
}
