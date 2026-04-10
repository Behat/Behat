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
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Exercise dispatching BEFORE/AFTER events during its execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template TSpec
 *
 * @implements Exercise<TSpec>
 */
final class EventDispatchingExercise implements Exercise
{
    /**
     * Initializes exercise.
     *
     * @param Exercise<TSpec>          $baseExercise
     */
    public function __construct(
        private readonly Exercise $baseExercise,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function setUp(array $iterators, bool $skip): Setup
    {
        $event = new BeforeExerciseCompleted($iterators);

        $this->eventDispatcher->dispatch($event, BeforeExerciseCompleted::BEFORE);

        $setup = $this->baseExercise->setUp($iterators, $skip);

        $event = new AfterExerciseSetup($iterators, $setup);

        $this->eventDispatcher->dispatch($event, AfterExerciseSetup::AFTER_SETUP);

        return $setup;
    }

    public function test(array $iterators, bool $skip = false): TestResult
    {
        return $this->baseExercise->test($iterators, $skip);
    }

    public function tearDown(array $iterators, bool $skip, TestResult $result): Teardown
    {
        $event = new BeforeExerciseTeardown($iterators, $result);

        $this->eventDispatcher->dispatch($event, BeforeExerciseTeardown::BEFORE_TEARDOWN);

        $teardown = $this->baseExercise->tearDown($iterators, $skip, $result);

        $event = new AfterExerciseCompleted($iterators, $result, $teardown);

        $this->eventDispatcher->dispatch($event, AfterExerciseCompleted::AFTER);

        return $teardown;
    }
}
