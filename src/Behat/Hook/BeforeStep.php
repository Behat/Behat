<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Hook;

use Attribute;

/**
 * Represents an Attribute for BeforeStep hook.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class BeforeStep implements Hook
{
    /**
     * @param string|null $filterString
     */
    public function __construct(
        public $filterString = null,
    ) {
    }

    public function getFilterString(): ?string
    {
        return $this->filterString;
    }
}
