<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Tester;

use Behat\Testwork\EventDispatcher\Event\AfterExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseSetup;
use Behat\Testwork\EventDispatcher\Event\BeforeExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\BeforeExerciseTeardown;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Exercise dispatching BEFORE/AFTER events during its execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EventDispatchingExercise implements Exercise
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
        $event = new BeforeExerciseCompleted($iterators);
        $this->eventDispatcher->dispatch($event::BEFORE, $event);

        $setup = $this->baseExercise->setUp($iterators, $skip);

        $event = new AfterExerciseSetup($iterators, $setup);
        $this->eventDispatcher->dispatch($event::AFTER_SETUP, $event);

        return $setup;
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
        $event = new BeforeExerciseTeardown($iterators, $result);
        $this->eventDispatcher->dispatch($event::BEFORE_TEARDOWN, $event);

        $teardown = $this->baseExercise->tearDown($iterators, $skip, $result);

        $event = new AfterExerciseCompleted($iterators, $result, $teardown);
        $this->eventDispatcher->dispatch($event::AFTER, $event);

        return $teardown;
    }
}
