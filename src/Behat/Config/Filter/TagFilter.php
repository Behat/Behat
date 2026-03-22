<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

/**
 * @api
 */
final class TagFilter extends Filter
{
    public const NAME = 'tags';

    /**
     * @api
     */
    public function __construct(
        string $value,
    ) {
        parent::__construct(self::NAME, $value);
    }
}
