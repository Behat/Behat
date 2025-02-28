<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

final class TagFilter extends Filter
{
    public const NAME = 'tags';

    public function __construct(
        string $value,
    ) {
        parent::__construct(self::NAME, $value);
    }
}
