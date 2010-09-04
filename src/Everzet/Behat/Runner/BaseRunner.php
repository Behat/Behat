<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

abstract class BaseRunner implements RunnerInterface, \Iterator
{
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

    public function getStatus()
    {
        $status = 'passed';

        foreach ($this->childs as $child) {
            $code = $this->getHigherStatus($status, $child->getStatus());
        }

        return $status;
    }

    protected function getHigherStatus($lftStatus, $rgtStatus)
    {
        $statuses   = array('passed', 'pending', 'undefined', 'failed');
        $code       = array_search($lftStatus, $statuses);

        if (($rgtCode = array_search($rgtStatus, $statuses)) > $code) {
            $code = $rgtCode;
        }

        return $statuses[$code];
    }

    public function getTime()
    {
        return $this->finishTime - $this->startTime;
    }

    public function fireEvent($eventName)
    {
        $this->dispatcher->notify(new Event($this, $this->runnerName . '.' . $eventName));
    }

    public function run()
    {
        $this->fireEvent('pre_test');
        $this->startTime = microtime(true);

        $status = $this->doRun();

        $this->finishTime = microtime(true);
        $this->fireEvent('post_test');

        return $status;
    }

    abstract protected function doRun();
}
