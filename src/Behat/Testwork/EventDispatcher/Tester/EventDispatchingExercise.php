<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Tester;

use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Testwork event-dispatching exercise.
 *
 * Exercise dispatching BEFORE/AFTER events during run.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatchingExercise implements Exercise
{
    /**
     * @var Exercise
     */
    private $baseExercise;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes exercise.
     *
     * @param Exercise                 $baseExercise
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Exercise $baseExercise, EventDispatcherInterface $eventDispatcher)
    {
        $this->baseExercise = $baseExercise;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(array $iterators, $skip)
    {
        $this->eventDispatcher->dispatch(ExerciseCompleted::BEFORE, new ExerciseCompleted());
        $this->baseExercise->setUp($iterators, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function test(array $iterators, $skip = false)
    {
        return $this->baseExercise->test($iterators, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(array $iterators, $skip, TestResult $result)
    {
        $this->eventDispatcher->dispatch(ExerciseCompleted::AFTER, new ExerciseCompleted($result));
        $this->baseExercise->tearDown($iterators, $skip, $result);
    }
}
