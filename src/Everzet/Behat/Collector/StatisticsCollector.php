<?php

namespace Everzet\Behat\Collector;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Tester\StepTester;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Collects run statistics during testsuite lifetime.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StatisticsCollector implements CollectorInterface
{
    protected $paused               = false;
    protected $suiteStartTime;
    protected $suiteFinishTime;
    protected $scenarioStartTime;
    protected $scenarioFinishTime;

    protected $isPassed             = true;
    protected $statuses             = array(
        StepTester::PASSED      => 'passed'
      , StepTester::SKIPPED     => 'skipped'
      , StepTester::PENDING     => 'pending'
      , StepTester::UNDEFINED   => 'undefined'
      , StepTester::FAILED      => 'failed'
    );

    protected $stepsCount           = 0;
    protected $scenariosCount       = 0;

    protected $stepsStatuses        = array();
    protected $scenariosStatuses    = array();

    protected $definitionsSnippets  = array();
    protected $failedStepsEvents    = array();
    protected $pendingStepsEvents   = array();

    /**
     * Initializes collector.
     */
    public function __construct()
    {
        $this->stepsStatuses = array_combine(
            array_values($this->statuses)
          , array_fill(0, count($this->statuses), 0)
        );
        $this->scenariosStatuses = array_combine(
            array_values($this->statuses)
          , array_fill(0, count($this->statuses), 0)
        );
    }

    /**
     * Start suite timer. 
     */
    public function startTimer()
    {
        $this->suiteStartTime = microtime(true);
    }

    /**
     * Stop suite timer. 
     */
    public function finishTimer()
    {
        $this->suiteFinishTime = microtime(true);
    }

    /**
     * Return suite total execution time. 
     * 
     * @return  integer miliseconds
     */
    public function getTotalTime()
    {
        return $this->suiteFinishTime - $this->suiteStartTime;
    }

    /**
     * Return last scenario execution time. 
     * 
     * @return  integer miliseconds
     */
    public function getLastScenarioTime()
    {
        return $this->scenarioFinishTime - $this->scenarioStartTime;
    }

    /**
     * Return true if suite passed. 
     * 
     * @return  boolean
     */
    public function isPassed()
    {
        return $this->isPassed;
    }

    /**
     * Return total steps count.
     *
     * @return  integer
     */
    public function getStepsCount()
    {
        return $this->stepsCount;
    }

    /**
     * Return total scenarios count.
     *
     * @return  integer
     */
    public function getScenariosCount()
    {
        return $this->scenariosCount;
    }

    /**
     * Return associative array of steps statuses count.
     *
     * @return  array       associative array (ex: passed => 10, failed => 2)
     */
    public function getStepsStatuses()
    {
        return $this->stepsStatuses;
    }

    /**
     * Return associative array of scenarios statuses count.
     *
     * @return  array       associative array (ex: passed => 10, failed => 2)
     */
    public function getScenariosStatuses()
    {
        return $this->scenariosStatuses;
    }

    /**
     * Return array of definition snippets for undefined steps.
     *
     * @return  array       associative array with md5 as key and snippet as value
     */
    public function getDefinitionsSnippets()
    {
        return $this->definitionsSnippets;
    }

    /**
     * Return array of failed steps events.
     *
     * @return  array
     */
    public function getFailedStepsEvents()
    {
        return $this->failedStepsEvents;
    }

    /**
     * Return array of pending steps events;
     *
     * @return  array
     */
    public function getPendingStepsEvents()
    {
        return $this->pendingStepsEvents;
    }

    /**
     * @see     Everzet\Behat\Collector\CollectorInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('scenario.run.before',     array($this, 'startScenarioTimer'),     10);
        $dispatcher->connect('outline.sub.run.before',  array($this, 'startScenarioTimer'),     10);
        $dispatcher->connect('scenario.run.after',      array($this, 'collectScenarioStats'),   10);
        $dispatcher->connect('outline.sub.run.after',   array($this, 'collectScenarioStats'),   10);
        $dispatcher->connect('step.run.after',          array($this, 'collectStepStats'),       10);
    }

    /**
     * Pause collecting.
     */
    public function pause()
    {
        $this->paused = true;
    }

    /**
     * Resume collecting.
     */
    public function resume()
    {
        $this->paused = false;
    }

    /**
     * Listens to `scenario.run.before` & `outline.sub.run.before` events & start scenario timers. 
     * 
     * @param   Event   $event  event
     */
    public function startScenarioTimer(Event $event)
    {
        $this->scenarioStartTime = microtime(true);
    }

    /**
     * Listens to `scenario.run.after` & `outline.sub.run.after` events & collect stats.
     *
     * @param   Event   $event  event
     */
    public function collectScenarioStats(Event $event)
    {
        if (!$this->paused) {
            ++$this->scenariosCount;
            ++$this->scenariosStatuses[$this->statuses[$event->get('result')]];
            if (0 !== $event->get('result')) {
                $this->isPassed = false;
            }
        }

        $this->scenarioFinishTime = microtime(true);
    }

    /**
     * Listens to `step.run.after` events & collect stats.
     *
     * @param   Event   $event  event
     */
    public function collectStepStats(Event $event)
    {
        if (!$this->paused) {
            ++$this->stepsCount;
            ++$this->stepsStatuses[$this->statuses[$event->get('result')]];
            if (0 !== $event->get('result')) {
                $this->isPassed = false;
            }

            if (StepTester::UNDEFINED === $event->get('result')) {
                foreach ($event->get('snippet') as $key => $snippet) {
                    if (!isset($this->definitionsSnippets[$key])) {
                        $this->definitionsSnippets[$key] = $snippet;
                    }
                }
            }

            if (StepTester::FAILED === $event->get('result')) {
                $this->failedStepsEvents[] = $event;
            }

            if (StepTester::PENDING === $event->get('result')) {
                $this->pendingStepsEvents[] = $event;
            }
        }
    }
}
