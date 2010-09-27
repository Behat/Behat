<?php

namespace Everzet\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\RunableNode\RunableNodeInterface;

abstract class BaseStatisticsFormatter
{
    protected $statuses         = array();
    protected $stepsCount       = 0;
    protected $scenariosCount   = 0;
    protected $stepsResults     = array();
    protected $scenariosResults = array();

    protected $snippets         = array();
    protected $failedSteps      = array();
    protected $pendingSteps     = array();

    protected function registerRunCounters(EventDispatcher $dispatcher)
    {
        $this->statuses = array(
            RunableNodeInterface::PASSED    => 'passed'
          , RunableNodeInterface::SKIPPED   => 'skipped'
          , RunableNodeInterface::PENDING   => 'pending'
          , RunableNodeInterface::UNDEFINED => 'undefined'
          , RunableNodeInterface::FAILED    => 'failed'
        );
        $this->stepsResults = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );
        $this->scenariosResults = array_combine(
            array_values($this->statuses),
            array_fill(0, count($this->statuses), 0)
        );

        $dispatcher->connect('scenario.run.after',  array($this, 'collectScenarioStats'),   9);
        $dispatcher->connect('step.run.after',      array($this, 'collectStepStats'),       9);
        $dispatcher->connect('step.skip.after',     array($this, 'collectStepStats'),       9);
    }

    public function collectScenarioStats(Event $event)
    {
        $scenario = $event->getSubject();

        ++$this->scenariosCount;
        ++$this->scenariosResults[$this->statuses[$scenario->getResult()]];
    }

    public function collectStepStats(Event $event)
    {
        $step = $event->getSubject();

        if (!($step->isInBackground() && $step->isPrintable())) {
            ++$this->stepsCount;
            ++$this->stepsResults[$this->statuses[$step->getResult()]];
        }

        if (RunableNodeInterface::UNDEFINED === $step->getResult()) {
            foreach ($step->getSnippet() as $key => $snippet) {
                if (!isset($this->snippets[$key])) {
                    $this->snippets[$key] = $snippet;
                }
            }
        }

        if (RunableNodeInterface::FAILED === $step->getResult()) {
            $this->failedSteps[] = clone $step;
        }

        if (RunableNodeInterface::PENDING === $step->getResult()) {
            $this->pendingSteps[] = clone $step;
        }
    }
}
