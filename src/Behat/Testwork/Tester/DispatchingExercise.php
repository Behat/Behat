<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Tester\Event\ExerciseCompleted;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Testwork dispatching exercise.
 *
 * Exercise dispatching BEFORE/AFTER events during run.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DispatchingExercise extends Exercise
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Sets event dispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function runExercise(array $subjectIterators, $skip = false)
    {
        $this->eventDispatcher && $this->eventDispatcher->dispatch(
            ExerciseCompleted::BEFORE,
            new ExerciseCompleted()
        );

        $result = parent::runExercise($subjectIterators, $skip);

        $this->eventDispatcher && $this->eventDispatcher->dispatch(
            ExerciseCompleted::AFTER,
            new ExerciseCompleted($result)
        );

        return $result;
    }
}
