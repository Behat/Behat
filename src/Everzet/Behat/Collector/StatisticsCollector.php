<?php

namespace Everzet\Behat\Collector;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Tester\StepTester;

class StatisticsCollector
{
    protected $statuses         = array();
    protected $stepsCount       = 0;
    protected $scenariosCount   = 0;
    protected $stepsResults     = array();
    protected $scenariosResults = array();

    protected $snippets         = array();
    protected $failedSteps      = array();
    protected $pendingSteps     = array();

    public function __construct()
    {
        $this->statuses = array(
            StepTester::PASSED      => 'passed'
          , StepTester::SKIPPED     => 'skipped'
          , StepTester::PENDING     => 'pending'
          , StepTester::UNDEFINED   => 'undefined'
          , StepTester::FAILED      => 'failed'
        );
        $this->stepsResults = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
        $this->scenariosResults = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
    }

    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('scenario.run.after',      array($this, 'collectScenarioStats'),   5);
        $dispatcher->connect('outline.sub.run.after',   array($this, 'collectScenarioStats'),   5);
        $dispatcher->connect('step.run.after',          array($this, 'collectStepStats'),       5);
    }

    public function collectScenarioStats(Event $event)
    {
        ++$this->scenariosCount;
        ++$this->scenariosResults[$this->statuses[$event['result']]];
    }

    public function collectStepStats(Event $event)
    {
        ++$this->stepsCount;
        ++$this->stepsResults[$this->statuses[$event['result']]];

        if (StepTester::UNDEFINED === $event['result']) {
            foreach ($event['snippet'] as $key => $snippet) {
                if (!isset($this->snippets[$key])) {
                    $this->snippets[$key] = $snippet;
                }
            }
        }

        if (StepTester::FAILED === $event['result']) {
            $this->failedSteps[] = $event;
        }

        if (StepTester::PENDING === $event['result']) {
            $this->pendingSteps[] = $event;
        }
    }
}
