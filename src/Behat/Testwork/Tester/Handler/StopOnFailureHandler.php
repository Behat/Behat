<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Handler;


use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseAborted;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteAborted;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Tester\Result\ResultInterpreter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Enables stop on failure via config.
 */
final class StopOnFailureHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ResultInterpreter $resultInterpreter
    ) {

    }
    
    public function registerListeners()
    {
        $this->eventDispatcher->addListener(ScenarioTested::AFTER, array($this, 'exitOnFailure'), -100);
        $this->eventDispatcher->addListener(ExampleTested::AFTER, array($this, 'exitOnFailure'), -100);
    }

    /**
     * Exits if scenario is a failure and if stopper is enabled.
     */
    public function exitOnFailure(AfterScenarioTested $event)
    {
        if (ResultInterpreter::PASS === $this->resultInterpreter->interpretResult($event->getTestResult())) {
            return;
        }

        $this->eventDispatcher->dispatch(new AfterSuiteAborted($event->getEnvironment()), SuiteTested::AFTER);
        $this->eventDispatcher->dispatch(new AfterExerciseAborted(), ExerciseCompleted::AFTER);

        exit(1);
    }
}