<?php

namespace Behat\Behat\DataCollector;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\Event\FeatureEvent,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\OutlineExampleEvent,
    Behat\Behat\Event\StepEvent;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat run logger.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class LoggerDataCollector implements EventSubscriberInterface
{
    /**
     * Suite run start time.
     *
     * @var     float
     */
    private $startTime;
    /**
     * Suite run finish time.
     *
     * @var     float
     */
    private $finishTime;
    /**
     * Step statuses text notations.
     *
     * @var     array
     *
     * @uses    Behat\Behat\Tester\StepEvent
     */
    private $statuses             = array(
        StepEvent::PASSED      => 'passed',
        StepEvent::SKIPPED     => 'skipped',
        StepEvent::PENDING     => 'pending',
        StepEvent::UNDEFINED   => 'undefined',
        StepEvent::FAILED      => 'failed'
    );
    /**
     * Overall suite result.
     *
     * @var     integer
     */
    private $suiteResult          = 0;
    /**
     * Overall features count.
     *
     * @var     integer
     */
    private $featuresCount        = 0;
    /**
     * All features statuses count.
     *
     * @var     array
     */
    private $featuresStatuses     = array();
    /**
     * Overall scenarios count.
     *
     * @var     integer
     */
    private $scenariosCount       = 0;
    /**
     * All scenarios statuses count.
     *
     * @var     array
     */
    private $scenariosStatuses    = array();
    /**
     * Overall steps count.
     *
     * @var     integer
     */
    private $stepsCount           = 0;
    /**
     * All steps statuses count.
     *
     * @var     array
     */
    private $stepsStatuses        = array();
    /**
     * Missed definitions snippets.
     *
     * @var     array
     */
    private $definitionsSnippets  = array();
    /**
     * Events of failed steps.
     *
     * @var     array
     */
    private $failedStepsEvents    = array();
    /**
     * Events of pending steps.
     *
     * @var     array
     */
    private $pendingStepsEvents   = array();

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
     * @see     Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */
    public static function getSubscribedEvents()
    {
        $events = array(
            'beforeSuite', 'afterSuite', 'afterFeature', 'afterScenario', 'afterOutlineExample',
            'afterStep'
        );

        return array_combine($events, $events);
    }

    /**
     * Listens to "suite.before" event.
     *
     * @param   Behat\Behat\Event\SuiteEvent    $event
     *
     * @uses    startTimer()
     */
    public function beforeSuite(SuiteEvent $event)
    {
        $this->startTimer();
    }

    /**
     * Listens to "suite.after" event.
     *
     * @param   Behat\Behat\Event\SuiteEvent    $event
     *
     * @uses    finishTimer()
     */
    public function afterSuite(SuiteEvent $event)
    {
        $this->finishTimer();
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param   Behat\Behat\Event\FeatureEvent  $event
     *
     * @uses    collectFeatureResult()
     */
    public function afterFeature(FeatureEvent $event)
    {
        $this->collectFeatureResult($event->getResult());
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param   Behat\Behat\Event\ScenarioEvent $event
     *
     * @uses    collectScenarioResult()
     */
    public function afterScenario(ScenarioEvent $event)
    {
        $this->collectScenarioResult($event->getResult());
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param   Behat\Behat\Event\OutlineExampleEvent   $event
     *
     * @uses    collectScenarioResult()
     */
    public function afterOutlineExample(OutlineExampleEvent $event)
    {
        $this->collectScenarioResult($event->getResult());
    }

    /**
     * Listens to "step.after" event.
     *
     * @param   Behat\Behat\Event\StepEvent $event
     *
     * @uses    collectStepStats()
     */
    public function afterStep(StepEvent $event)
    {
        $this->collectStepStats($event);
    }

    /**
     * Returns suite total execution time.
     *
     * @return  float   miliseconds
     */
    public function getTotalTime()
    {
        return $this->finishTime - $this->startTime;
    }

    /**
     * Returns overall suites result.
     *
     * @return  integer
     */
    public function getSuiteResult()
    {
        return $this->suiteResult;
    }

    /**
     * Returns overall features count.
     *
     * @return  integer
     */
    public function getFeaturesCount()
    {
        return $this->featuresCount;
    }

    /**
     * Returns hash of features statuses count.
     *
     * @return  array       hash (ex: passed => 10, failed => 2)
     */
    public function getFeaturesStatuses()
    {
        return $this->featuresStatuses;
    }

    /**
     * Returns overall scenarios count.
     *
     * @return  integer
     */
    public function getScenariosCount()
    {
        return $this->scenariosCount;
    }

    /**
     * Returns hash of scenarios statuses count.
     *
     * @return  array       hash (ex: passed => 10, failed => 2)
     */
    public function getScenariosStatuses()
    {
        return $this->scenariosStatuses;
    }

    /**
     * Returns overall steps count.
     *
     * @return  integer
     */
    public function getStepsCount()
    {
        return $this->stepsCount;
    }

    /**
     * Returns hash of steps statuses count.
     *
     * @return  array       hash (ex: passed => 10, failed => 2)
     */
    public function getStepsStatuses()
    {
        return $this->stepsStatuses;
    }

    /**
     * Returns hash of definition snippets for undefined steps.
     *
     * @return  array       hash with md5 as key and snippet as value
     */
    public function getDefinitionsSnippets()
    {
        return $this->definitionsSnippets;
    }

    /**
     * Returns array of failed steps events.
     *
     * @return  array
     */
    public function getFailedStepsEvents()
    {
        return $this->failedStepsEvents;
    }

    /**
     * Returns array of pending steps events;
     *
     * @return  array
     */
    public function getPendingStepsEvents()
    {
        return $this->pendingStepsEvents;
    }

    /**
     * Starts suite timer.
     */
    private function startTimer()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Stops suite timer.
     */
    private function finishTimer()
    {
        $this->finishTime = microtime(true);
    }

    /**
     * Collects feature result status.
     *
     * @param   integer $result status code
     */
    private function collectFeatureResult($result)
    {
        ++$this->featuresCount;
        ++$this->featuresStatuses[$this->statuses[$result]];

        $this->suiteResult = max($this->suiteResult, $result);
    }

    /**
     * Collects scenario result status.
     *
     * @param   integer $result status code
     */
    private function collectScenarioResult($result)
    {
        ++$this->scenariosCount;
        ++$this->scenariosStatuses[$this->statuses[$result]];
    }

    /**
     * Collects step statistics.
     *
     * @param   Behat\Behat\Event\StepEvent $event  step.after event
     */
    private function collectStepStats(StepEvent $event)
    {
        ++$this->stepsCount;
        ++$this->stepsStatuses[$this->statuses[$event->getResult()]];

        switch ($event->getResult()) {
            case StepEvent::UNDEFINED:
                $hash = $event->getSnippet()->getHash();
                if (!isset($this->definitionsSnippets[$hash])) {
                    $this->definitionsSnippets[$hash] = $event->getSnippet();
                } else {
                    $this->definitionsSnippets[$hash]->addStep($event->getSnippet()->getLastStep());
                }
                break;
            case StepEvent::FAILED:
                $this->failedStepsEvents[] = $event;
                break;
            case StepEvent::PENDING:
                $this->pendingStepsEvents[] = $event;
                break;
        }
    }
}
