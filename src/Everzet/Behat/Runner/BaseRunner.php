<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

abstract class BaseRunner implements RunnerInterface, \Iterator
{
    protected static $statuses = array('passed', 'skipped', 'pending', 'undefined', 'failed');

    protected $runnerName;
    protected $dispatcher;

    protected $parent;
    protected $childs = array();

    protected $startTime;
    protected $finishTime;

    public function __construct($runnerName, EventDispatcher $dispatcher,
                                RunnerInterface $parent = null)
    {
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
        $status = 'passed';

        foreach ($this as $child) {
            $status = $this->getHigherStatus($status, $child->getStatus());
        }

        return $status;
    }

    public function getStatusCode()
    {
        return array_search($this->getStatus(), self::$statuses);
    }

    public function getTime()
    {
        return $this->finishTime - $this->startTime;
    }

    public function run()
    {
        $this->fireEvent('pre_test');
        $this->startTime = microtime(true);

        $this->doRun();

        $this->finishTime = microtime(true);
        $this->fireEvent('post_test');

        return $this->getStatusCode();
    }

    protected function getHigherStatus($lftStatus, $rgtStatus)
    {
        $code = array_search($lftStatus, self::$statuses);

        if (($rgtCode = array_search($rgtStatus, self::$statuses)) > $code) {
            $code = $rgtCode;
        }

        return self::$statuses[$code];
    }

    protected function fireEvent($eventName)
    {
        $this->dispatcher->notify(new Event($this, $this->runnerName . '.' . $eventName));
    }

    abstract protected function doRun();
}
