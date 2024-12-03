<?php

declare(strict_types=1);

namespace Behat\Config\Gherkin\Filter;

interface FilterInterface
{
    public function name(): string;

    public function value(): mixed;
}
