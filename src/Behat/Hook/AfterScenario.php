<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Hook;

/**
 * Represents an Attribute for AfterScenario hook
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AfterScenario implements Hook
{
    /**
     * @var string|null
     */
    public $filterString;

    public function __construct($filterString = null)
    {
        $this->filterString = $filterString;
    }

    public function getFilterString(): ?string
    {
        return $this->filterString;
    }
}
