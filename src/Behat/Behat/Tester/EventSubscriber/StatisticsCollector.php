<?php

namespace Behat\Behat\Tester\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\ExerciseEvent;
use Behat\Behat\Event\FeatureEvent;
use Behat\Behat\Event\StepCollectionEvent;
use Behat\Behat\Event\StepEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Statistics collector.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StatisticsCollector implements EventSubscriberInterface
{
    private $startTime;
    private $finishTime;
    private $statuses = array(
        StepEvent::PASSED    => 'passed',
        StepEvent::SKIPPED   => 'skipped',
        StepEvent::PENDING   => 'pending',
        StepEvent::UNDEFINED => 'undefined',
        StepEvent::FAILED    => 'failed'
    );
    private $suiteResult = 0;
    private $featuresCount = 0;
    private $featuresStatuses = array();
    private $scenariosCount = 0;
    private $scenariosStatuses = array();
    private $stepsCount = 0;
    private $stepsStatuses = array();
    private $failedStepsEvents = array();
    private $pendingStepsEvents = array();

    /**
     * Initializes logger.
     */
    public function __construct()
    {
        $this->featuresStatuses = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
        $this->scenariosStatuses = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
        $this->stepsStatuses = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::BEFORE_EXERCISE       => array('startTimer', -10),
            EventInterface::AFTER_EXERCISE        => array('finishTimer', -10),
            EventInterface::AFTER_FEATURE         => array('collectFeatureResult', -10),
            EventInterface::AFTER_SCENARIO        => array('collectScenarioResult', -10),
            EventInterface::AFTER_OUTLINE_EXAMPLE => array('collectScenarioResult', -10),
            EventInterface::AFTER_STEP            => array('collectStepStats', -10),
        );
    }

    /**
     * @param ExerciseEvent $event
     */
    public function startTimer(ExerciseEvent $event)
    {
        $this->startTime = microtime(true);
    }

    /**
     * @param ExerciseEvent $event
     */
    public function finishTimer(ExerciseEvent $event)
    {
        $this->finishTime = microtime(true);
    }

    /**
     * @param FeatureEvent $event
     */
    public function collectFeatureResult(FeatureEvent $event)
    {
        ++$this->featuresCount;
        ++$this->featuresStatuses[$this->statuses[$event->getResult()]];

        $this->suiteResult = max($this->suiteResult, $event->getResult());
    }

    /**
     * @param StepCollectionEvent $event
     */
    public function collectScenarioResult(StepCollectionEvent $event)
    {
        ++$this->scenariosCount;
        ++$this->scenariosStatuses[$this->statuses[$event->getResult()]];
    }

    /**
     * @param StepEvent $event
     */
    public function collectStepStats(StepEvent $event)
    {
        ++$this->stepsCount;
        ++$this->stepsStatuses[$this->statuses[$event->getResult()]];

        switch ($event->getResult()) {
            case StepEvent::FAILED:
                $this->failedStepsEvents[] = $event;
                break;
            case StepEvent::PENDING:
                $this->pendingStepsEvents[] = $event;
                break;
        }
    }

    /**
     * Returns suite total execution time.
     *
     * @return float miliseconds
     */
    public function getTotalTime()
    {
        return $this->finishTime - $this->startTime;
    }

    /**
     * Returns overall suites result.
     *
     * @return integer
     */
    public function getSuiteResult()
    {
        return $this->suiteResult;
    }

    /**
     * Returns overall features count.
     *
     * @return integer
     */
    public function getFeaturesCount()
    {
        return $this->featuresCount;
    }

    /**
     * Returns hash of features statuses count.
     *
     * @return array hash (ex: passed => 10, failed => 2)
     */
    public function getFeaturesStatuses()
    {
        return $this->featuresStatuses;
    }

    /**
     * Returns overall scenarios count.
     *
     * @return integer
     */
    public function getScenariosCount()
    {
        return $this->scenariosCount;
    }

    /**
     * Returns hash of scenarios statuses count.
     *
     * @return array hash (ex: passed => 10, failed => 2)
     */
    public function getScenariosStatuses()
    {
        return $this->scenariosStatuses;
    }

    /**
     * Returns overall steps count.
     *
     * @return integer
     */
    public function getStepsCount()
    {
        return $this->stepsCount;
    }

    /**
     * Returns hash of steps statuses count.
     *
     * @return array hash (ex: passed => 10, failed => 2)
     */
    public function getStepsStatuses()
    {
        return $this->stepsStatuses;
    }

    /**
     * Returns array of failed steps events.
     *
     * @return array
     */
    public function getFailedStepsEvents()
    {
        return $this->failedStepsEvents;
    }

    /**
     * Returns array of pending steps events;
     *
     * @return array
     */
    public function getPendingStepsEvents()
    {
        return $this->pendingStepsEvents;
    }
}
