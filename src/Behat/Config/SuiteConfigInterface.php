<?php

declare(strict_types=1);

namespace Behat\Config;

interface SuiteConfigInterface extends ConfigInterface
{
    public function name(): string;

    public function profile(): ?string;
}
