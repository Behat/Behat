<?php

declare(strict_types=1);

final class SharedServiceExpecting1 extends SharedService
{
    public function __construct(int $arg)
    {
        if (1 !== $arg) {
            throw new InvalidArgumentException();
        }
    }
}
