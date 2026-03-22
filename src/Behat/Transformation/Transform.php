<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Transformation;

use Attribute;

/**
 * Represents an Attribute for a Transform transformation.
 *
 * @api
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Transform
{
    /**
     * @api
     */
    public function __construct(
        public readonly string $pattern = '',
    ) {
    }
}
