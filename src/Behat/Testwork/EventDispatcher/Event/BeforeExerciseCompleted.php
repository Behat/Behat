<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Tester\Context\ExerciseContext;

/**
 * Represents an event in which exercise is prepared to be executed.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeExerciseCompleted extends ExerciseCompleted implements BeforeTested
{
    /**
     * @var GroupedSpecificationIterator[]
     */
    private $specificationIterators;

    /**
     * Initializes event.
     *
     * @param ExerciseContext $context
     */
    public function __construct(ExerciseContext $context)
    {
        $this->specificationIterators = $context->getGroupedSpecificationIterators();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return self::BEFORE;
    }

    /**
     * Returns specification iterators.
     *
     * @return GroupedSpecificationIterator[]
     */
    public function getSpecificationIterators()
    {
        return $this->specificationIterators;
    }
}
