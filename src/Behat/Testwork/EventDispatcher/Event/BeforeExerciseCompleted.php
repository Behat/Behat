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

/**
 * Represents an event in which exercise is prepared to be executed.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeExerciseCompleted extends ExerciseCompleted implements BeforeTested
{
    /**
     * @var SpecificationIterator<mixed>[]
     */
    private $specificationIterators;

    /**
     * Initializes event.
     *
     * @param SpecificationIterator<mixed>[] $specificationIterators
     */
    public function __construct(array $specificationIterators)
    {
        $this->specificationIterators = $specificationIterators;
    }

    public function getSpecificationIterators()
    {
        return $this->specificationIterators;
    }
}
