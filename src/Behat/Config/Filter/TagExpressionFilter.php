<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

/**
 * @api
 */
final class TagExpressionFilter extends Filter
{
    public const NAME = 'tag_expression';

    /**
     * @api
     */
    public function __construct(
        string $value,
    ) {
        parent::__construct(self::NAME, $value);
    }
}
