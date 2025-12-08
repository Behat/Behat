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
     * Initializes event.
     *
     * @param SpecificationIterator<mixed>[] $specificationIterators
     */
    public function __construct(
        private readonly array $specificationIterators,
        private readonly Setup $setup,
    ) {
    }

    /**
     * @return SpecificationIterator[]
     */
    public function getSpecificationIterators(): array
    {
        return $this->specificationIterators;
    }

    /**
     * Returns exercise setup result.
     */
    public function getSetup(): Setup
    {
        return $this->setup;
    }
}
