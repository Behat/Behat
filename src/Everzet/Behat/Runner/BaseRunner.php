<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

abstract class BaseRunner implements RunnerInterface, \Iterator
{
    protected static $statuses = array(
        0 => 'passed'
      , 1 => 'skipped'
      , 2 => 'pending'
      , 3 => 'undefined'
      , 4 => 'failed'
    );
    protected $statusCode = 0;

    protected $runnerName;
    protected $dispatcher;

    protected $parent;
    protected $childs = array();

    protected $startTime;
    protected $finishTime;

    public function __construct($runnerName, EventDispatcher $dispatcher,
                                RunnerInterface $parent = null)
    {
        $this->statusCode   = 0;
        $this->runnerName   = $runnerName;
        $this->dispatcher   = $dispatcher;
        $this->parent       = $parent;
    }

    public function getParentRunner()
    {
        return $this->parent;
    }

    public function addChildRunner(RunnerInterface $child)
    {
        $this->childs[] = $child;
    }

    public function getChildRunners()
    {
        return $this->childs;
    }

    public function key()
    {
        return $this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->childs[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->childs[$this->position]);
    }

    public function getFailedStepRunners()
    {
        $runners = array();

        foreach ($this as $child) {
            $runners = array_merge($runners, $child->getFailedStepRunners());
        }

        return $runners;
    }

    public function getPendingStepRunners()
    {
        $runners = array();

        foreach ($this as $child) {
            $runners = array_merge($runners, $child->getPendingStepRunners());
        }

        return $runners;
    }

    public function getDefinitionSnippets()
    {
        $snippets = array();

        foreach ($this as $child) {
            $snippets = array_merge($snippets, $child->getDefinitionSnippets());
        }

        return $snippets;
    }

    public function getScenariosCount()
    {
        $count = 0;

        foreach ($this as $child) {
            $count += $child->getScenariosCount();
        }

        return $count;
    }

    public function getStepsCount()
    {
        $count = 0;

        foreach ($this as $child) {
            $count += $child->getStepsCount();
        }

        return $count;
    }

    public function getScenariosStatusesCount()
    {
        $statuses = array();

        foreach ($this as $child) {
            foreach ($child->getScenariosStatusesCount() as $status => $count) {
                if (!isset($statuses[$status])) {
                    $statuses[$status] = 0;
                }

                $statuses[$status] += $count;
            }
        }

        return $statuses;
    }

    public function getStepsStatusesCount()
    {
        $statuses = array();

        foreach ($this as $child) {
            foreach ($child->getStepsStatusesCount() as $status => $count) {
                if (!isset($statuses[$status])) {
                    $statuses[$status] = 0;
                }

                $statuses[$status] += $count;
            }
        }

        return $statuses;
    }

    public function getStatus()
    {
        return $this->codeToStatus($this->statusCode);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getTime()
    {
        return $this->finishTime - $this->startTime;
    }

    public function run()
    {
        $this->fireEvent('pre_test');
        $this->startTime = microtime(true);

        $status = $this->doRun();

        $this->finishTime = microtime(true);
        $this->fireEvent('post_test');

        return $this->statusCode = $status;
    }

    protected function statusToCode($status)
    {
        return array_search($status, self::$statuses);
    }

    protected function codeToStatus($code)
    {
        return self::$statuses[$code];
    }

    protected function fireEvent($eventName)
    {
        $this->dispatcher->notify(new Event($this, $this->runnerName . '.' . $eventName));
    }

    abstract protected function doRun();
}
