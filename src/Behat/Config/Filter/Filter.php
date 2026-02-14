<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

class Filter implements FilterInterface
{
    public function __construct(
        private readonly string $name,
        private readonly string $value,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->value;
    }
}
