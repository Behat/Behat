<?php

namespace Everzet\Behat\Collector;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Tester\StepTester;

class StatisticsCollector
{
    protected $paused               = false;

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

    public function getStepsCount()
    {
        return $this->stepsCount;
    }

    public function getScenariosCount()
    {
        return $this->scenariosCount;
    }

    public function getStepsStatuses()
    {
        return $this->stepsStatuses;
    }

    public function getScenariosStatuses()
    {
        return $this->scenariosStatuses;
    }

    public function getDefinitionsSnippets()
    {
        return $this->definitionsSnippets;
    }

    public function getFailedStepsEvents()
    {
        return $this->failedStepsEvents;
    }

    public function getPendingStepsEvents()
    {
        return $this->pendingStepsEvents;
    }

    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('scenario.run.after',      array($this, 'collectScenarioStats'),   5);
        $dispatcher->connect('outline.sub.run.after',   array($this, 'collectScenarioStats'),   5);
        $dispatcher->connect('step.run.after',          array($this, 'collectStepStats'),       5);
    }

    public function pause()
    {
        $this->paused = true;
    }

    public function resume()
    {
        $this->paused = false;
    }

    public function collectScenarioStats(Event $event)
    {
        if (!$this->paused) {
            ++$this->scenariosCount;
            ++$this->scenariosStatuses[$this->statuses[$event['result']]];
        }
    }

    public function collectStepStats(Event $event)
    {
        if (!$this->paused) {
            ++$this->stepsCount;
            ++$this->stepsStatuses[$this->statuses[$event['result']]];

            if (StepTester::UNDEFINED === $event['result']) {
                foreach ($event['snippet'] as $key => $snippet) {
                    if (!isset($this->definitionsSnippets[$key])) {
                        $this->definitionsSnippets[$key] = $snippet;
                    }
                }
            }

            if (StepTester::FAILED === $event['result']) {
                $this->failedStepsEvents[] = $event;
            }

            if (StepTester::PENDING === $event['result']) {
                $this->pendingStepsEvents[] = $event;
            }
        }
    }
}
