<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Context;

use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Specification\SpecificationIterator;

/**
 * Encapsulates an exercise context.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ExerciseContext implements Context
{
    /**
     * @var SpecificationIterator[]
     */
    private $iterators;
    /**
     * @var GroupedSpecificationIterator[]
     */
    private $grouped;

    /**
     * Initializes context.
     *
     * @param SpecificationIterator[] $iterators
     */
    public function __construct(array $iterators)
    {
        $this->iterators = $iterators;
    }

    /**
     * Returns grouped specification iterators.
     *
     * @return GroupedSpecificationIterator[]
     */
    public function getGroupedSpecificationIterators()
    {
        return $this->grouped = $this->grouped
            ?: GroupedSpecificationIterator::group($this->iterators);
    }
}
