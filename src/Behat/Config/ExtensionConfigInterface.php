<?php

declare(strict_types=1);

namespace Behat\Config;

/**
 * @api
 */
interface ExtensionConfigInterface extends ConfigInterface
{
    public function name(): string;
}
