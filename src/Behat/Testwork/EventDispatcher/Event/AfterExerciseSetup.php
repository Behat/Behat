<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event in which exercise is prepared to be executed.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterExerciseSetup extends ExerciseCompleted implements AfterSetup
{
    /**
     * @var SpecificationIterator[]
     */
    private $specificationIterators;
    /**
     * @var Setup
     */
    private $setup;

    /**
     * Initializes event.
     *
     * @param SpecificationIterator[] $specificationIterators
     * @param Setup                   $setup
     */
    public function __construct(array $specificationIterators, Setup $setup)
    {
        $this->specificationIterators = $specificationIterators;
        $this->setup = $setup;
    }

    /**
     * Returns specification iterators.
     *
     * @return SpecificationIterator[]
     */
    public function getSpecificationIterators()
    {
        return $this->specificationIterators;
    }

    /**
     * Returns exercise setup result.
     *
     * @return Setup
     */
    public function getSetup()
    {
        return $this->setup;
    }
}
