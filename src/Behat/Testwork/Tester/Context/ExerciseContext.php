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
 * Represents a context for specification tests.
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
        return GroupedSpecificationIterator::group($this->iterators);
    }
}
