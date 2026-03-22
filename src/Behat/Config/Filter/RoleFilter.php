<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

/**
 * @api
 */
final class RoleFilter extends Filter
{
    public const NAME = 'role';

    /**
     * @api
     */
    public function __construct(
        string $value,
    ) {
        parent::__construct(self::NAME, $value);
    }
}
