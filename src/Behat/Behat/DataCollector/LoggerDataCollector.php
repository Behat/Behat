<?php

namespace Behat\Behat\DataCollector;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Tester\StepTester;

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
class LoggerDataCollector
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
     * @uses    Behat\Behat\Tester\StepTester
     */
    protected $statuses             = array(
        StepTester::PASSED      => 'passed',
        StepTester::SKIPPED     => 'skipped',
        StepTester::PENDING     => 'pending',
        StepTester::UNDEFINED   => 'undefined',
        StepTester::FAILED      => 'failed'
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
     * Registers logger event listeners.
     *
     * @param   Behat\Behat\EventDispatcher\EventDispatcher $dispatcher
     *
     * @uses    beforeSuite()
     * @uses    afterSuite()
     * @uses    afterScenario()
     * @uses    afterOutlineExample()
     * @uses    afterStep()
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('suite.before',            array($this, 'beforeSuite'),            0);
        $dispatcher->connect('suite.after',             array($this, 'afterSuite'),             0);
        $dispatcher->connect('scenario.after',          array($this, 'afterScenario'),          0);
        $dispatcher->connect('outline.example.after',   array($this, 'afterOutlineExample'),    0);
        $dispatcher->connect('step.after',              array($this, 'afterStep'),              0);
    }

    /**
     * Listens to "suite.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event $event
     *
     * @uses    startTimer()
     */
    public function beforeSuite(Event $event)
    {
        $this->startTimer();
    }

    /**
     * Listens to "suite.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event $event
     *
     * @uses    finishTimer()
     */
    public function afterSuite(Event $event)
    {
        $this->finishTimer();
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event $event
     *
     * @uses    collectScenarioResult()
     */
    public function afterScenario(Event $event)
    {
        $this->collectScenarioResult($event->get('result'));
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event $event
     *
     * @uses    collectScenarioResult()
     */
    public function afterOutlineExample(Event $event)
    {
        $this->collectScenarioResult($event->get('result'));
    }

    /**
     * Listens to "step.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event $event
     *
     * @uses    collectStepStats()
     */
    public function afterStep(Event $event)
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
     * @param   Symfony\Component\EventDispatcher\Event $event  step.after event
     */
    protected function collectStepStats(Event $event)
    {
        ++$this->stepsCount;
        ++$this->stepsStatuses[$this->statuses[$event->get('result')]];

        switch ($event->get('result')) {
            case StepTester::UNDEFINED:
                foreach ($event->get('snippet') as $key => $snippet) {
                    if (!isset($this->definitionsSnippets[$key])) {
                        $this->definitionsSnippets[$key] = $snippet;
                    }
                }
                break;
            case StepTester::FAILED:
                $this->failedStepsEvents[] = $event;
                break;
            case StepTester::PENDING:
                $this->pendingStepsEvents[] = $event;
                break;
        }
    }
}
