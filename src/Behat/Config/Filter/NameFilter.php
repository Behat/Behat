<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

final class NameFilter extends Filter
{
    public const NAME = 'name';

    public function __construct(
        string $value,
    ) {
        parent::__construct(self::NAME, $value);
    }
}
