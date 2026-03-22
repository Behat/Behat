<?php

declare(strict_types=1);

namespace Behat\Config;

/**
 * @api
 */
interface ConfigInterface
{
    public function toArray(): array;
}
