<?php

namespace Behat\Behat\DataCollector;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\Event\SuiteEvent,
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
    protected $startTime;
    /**
     * Suite run finish time.
     *
     * @var     float
     */
    protected $finishTime;
    /**
     * Step statuses text notations.
     *
     * @var     array
     *
     * @uses    Behat\Behat\Tester\StepEvent
     */
    protected $statuses             = array(
        StepEvent::PASSED      => 'passed',
        StepEvent::SKIPPED     => 'skipped',
        StepEvent::PENDING     => 'pending',
        StepEvent::UNDEFINED   => 'undefined',
        StepEvent::FAILED      => 'failed'
    );
    /**
     * Overall steps count.
     *
     * @var     integer
     */
    protected $stepsCount           = 0;
    /**
     * Overall scenarios count.
     *
     * @var     integer
     */
    protected $scenariosCount       = 0;
    /**
     * All steps statuses count.
     *
     * @var     array
     */
    protected $stepsStatuses        = array();
    /**
     * All scenarios statuses count.
     *
     * @var     array
     */
    protected $scenariosStatuses    = array();
    /**
     * Missed definitions snippets.
     *
     * @var     array
     */
    protected $definitionsSnippets  = array();
    /**
     * Events of failed steps.
     *
     * @var     array
     */
    protected $failedStepsEvents    = array();
    /**
     * Events of pending steps.
     *
     * @var     array
     */
    protected $pendingStepsEvents   = array();

    /**
     * Initializes logger.
     */
    public function __construct()
    {
        $this->stepsStatuses = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
        $this->scenariosStatuses = array_combine(
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
            'beforeSuite', 'afterSuite', 'afterScenario', 'afterOutlineExample', 'afterStep'
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
     * Returns overall steps count.
     *
     * @return  integer
     */
    public function getStepsCount()
    {
        return $this->stepsCount;
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
     * Returns hash of steps statuses count.
     *
     * @return  array       hash (ex: passed => 10, failed => 2)
     */
    public function getStepsStatuses()
    {
        return $this->stepsStatuses;
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
    protected function startTimer()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Stops suite timer.
     */
    protected function finishTimer()
    {
        $this->finishTime = microtime(true);
    }

    /**
     * Collects scenario result status.
     *
     * @param   integer $result status code
     */
    protected function collectScenarioResult($result)
    {
        ++$this->scenariosCount;
        ++$this->scenariosStatuses[$this->statuses[$result]];
    }

    /**
     * Collects step statistics.
     *
     * @param   Behat\Behat\Event\StepEvent $event  step.after event
     */
    protected function collectStepStats(StepEvent $event)
    {
        ++$this->stepsCount;
        ++$this->stepsStatuses[$this->statuses[$event->getResult()]];

        switch ($event->getResult()) {
            case StepEvent::UNDEFINED:
                foreach ($event->getSnippet() as $key => $snippet) {
                    if (!isset($this->definitionsSnippets[$key])) {
                        $this->definitionsSnippets[$key] = $snippet;
                    }
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
