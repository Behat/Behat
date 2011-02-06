<?php

namespace Behat\Behat\DataCollector;

use Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Tester\StepTester;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Collects run statistics during testsuite lifetime.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class LoggerDataCollector
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
     * Return suite total execution time. 
     * 
     * @return  integer miliseconds
     */
    public function getTotalTime()
    {
        return $this->suiteFinishTime - $this->suiteStartTime;
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

    public function beforeSuite(Event $event)
    {
        $this->startTimer();
    }

    public function afterSuite(Event $event)
    {
        $this->finishTimer();
    }

    public function afterScenario(Event $event)
    {
        $this->collectScenarioResult($event->get('result'));
    }

    public function afterOutlineExample(Event $event)
    {
        $this->collectScenarioResult($event->get('result'));
    }

    public function afterStep(Event $event)
    {
        $this->collectStepStats($event);
    }

    protected function collectScenarioResult($result)
    {
        ++$this->scenariosCount;
        ++$this->scenariosStatuses[$this->statuses[$result]];
        if (0 !== $result) {
            $this->isPassed = false;
        }
    }

    protected function collectStepStats(Event $event)
    {
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

    /**
     * Start suite timer.
     */
    protected function startTimer()
    {
        $this->suiteStartTime = microtime(true);
    }

    /**
     * Stop suite timer.
     */
    protected function finishTimer()
    {
        $this->suiteFinishTime = microtime(true);
    }
}
