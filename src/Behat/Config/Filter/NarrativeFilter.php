<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

/**
 * @api
 */
final class NarrativeFilter extends Filter
{
    public const NAME = 'narrative';

    /**
     * @api
     */
    public function __construct(
        string $value,
    ) {
        parent::__construct(self::NAME, $value);
    }
}
