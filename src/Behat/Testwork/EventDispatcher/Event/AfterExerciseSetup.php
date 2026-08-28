<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Event\Event;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event in which exercise is prepared to be executed.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
final class AfterExerciseSetup extends Event implements ExerciseCompleted, AfterSetup
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
