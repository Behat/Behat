<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Configuration\Handler;

use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseAborted;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteAborted;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Tester\Result\Interpretation\ResultInterpretation;
use Behat\Testwork\Tester\Result\Interpretation\StrictInterpretation;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Enables stop on failure via configuration.
 */
final class StopOnFailureHandler implements EventSubscriberInterface
{
    
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ResultInterpretation
     */
    private $resultInterpretation;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->resultInterpretation = new StrictInterpretation();
    }

    public static function getSubscribedEvents()
    {
        return array(
            ScenarioTested::AFTER => array('exitOnFailure', -100),
            ExampleTested::AFTER => array('exitOnFailure', -100),
        );
    }
    

    /**
     * Exits if scenario is a failure and if stopper is enabled.
     *
     * @param AfterScenarioTested $event
     */
    public function exitOnFailure(AfterScenarioTested $event)
    {
        if (!$this->resultInterpretation->isFailure($event->getTestResult())) {
            return;
        }

        $this->eventDispatcher->dispatch(new AfterSuiteAborted($event->getEnvironment()), SuiteTested::AFTER);
        $this->eventDispatcher->dispatch(new AfterExerciseAborted(), ExerciseCompleted::AFTER);

        exit(1);
    }
}