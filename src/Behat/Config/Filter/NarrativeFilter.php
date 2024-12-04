<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

final class NarrativeFilter implements FilterInterface
{
    public function __construct(
        private readonly string $value,
    ) {
    }

    public function name(): string
    {
        return 'narrative';
    }

    public function value(): string
    {
        return $this->value;
    }
}
