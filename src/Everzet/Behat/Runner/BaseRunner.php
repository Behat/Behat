<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract runner instance.
 * Implements base runners actions.
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
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

    /**
     * Creates runner instance
     *
     * @param   string          $runnerName runner name (used in event firing)
     * @param   EventDispatcher $dispatcher event dispatcher instance 
     * @param   RunnerInterface $parent     parent runner
     */
    public function __construct($runnerName, EventDispatcher $dispatcher,
                                RunnerInterface $parent = null)
    {
        $this->statusCode   = 0;
        $this->runnerName   = $runnerName;
        $this->dispatcher   = $dispatcher;
        $this->parent       = $parent;
    }

    /**
     * Returns parent runner
     *
     * @return  RunnerInterface
     */
    public function getParentRunner()
    {
        return $this->parent;
    }

    /**
     * Adds child runner to batch
     *
     * @param   RunnerInterface $child  child runner (scenarios for features, steps for scenarios)
     */
    public function addChildRunner(RunnerInterface $child)
    {
        $this->childs[] = $child;
    }

    /**
     * Returns child runners array
     *
     * @return  array   array of RunnerInterface
     */
    public function getChildRunners()
    {
        return $this->childs;
    }

    /**
     * Sets child runners array
     *
     * @param   array   $runners    array of RunnerInstance
     */
    protected function setChildRunners(array $runners)
    {
        $this->childs = $runners;
    }

    /**
     * @see \Iterator
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @see \Iterator
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @see \Iterator
     */
    public function current()
    {
        return $this->childs[$this->position];
    }

    /**
     * @see \Iterator
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @see \Iterator
     */
    public function valid()
    {
        return isset($this->childs[$this->position]);
    }

    /**
     * Returns array of failed steps runners instances
     *
     * @return  array   array of RunnerInterface
     */
    public function getFailedStepRunners()
    {
        $runners = array();

        foreach ($this as $child) {
            $runners = array_merge($runners, $child->getFailedStepRunners());
        }

        return $runners;
    }

    /**
     * Returns array of pending steps runners instances
     *
     * @return  array   array of RunnerInterface
     */
    public function getPendingStepRunners()
    {
        $runners = array();

        foreach ($this as $child) {
            $runners = array_merge($runners, $child->getPendingStepRunners());
        }

        return $runners;
    }

    /**
     * Returns array of definition snippets for undefined steps
     *
     * @return  array   array of items (md5_key => definition)
     */
    public function getDefinitionSnippets()
    {
        $snippets = array();

        foreach ($this as $child) {
            $snippets = array_merge($child->getDefinitionSnippets(), $snippets);
        }

        return $snippets;
    }

    /**
     * Returns overall scenarios count
     *
     * @return  integer     count
     */
    public function getScenariosCount()
    {
        $count = 0;

        foreach ($this as $child) {
            $count += $child->getScenariosCount();
        }

        return $count;
    }

    /**
     * Returns overall steps count
     *
     * @return  integer     count
     */
    public function getStepsCount()
    {
        $count = 0;

        foreach ($this as $child) {
            $count += $child->getStepsCount();
        }

        return $count;
    }

    /**
     * Returns associative array of scenario statuses count
     *
     * @return  array   array of items (status => count)
     */
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

    /**
     * Returns associative array of step statuses count
     *
     * @return  array   array of items (status => count)
     */
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

    /**
     * @see Everzet\Behat\Runner\RunnerInterface
     */
    public function getStatus()
    {
        return $this->codeToStatus($this->statusCode);
    }

    /**
     * @see Everzet\Behat\Runner\RunnerInterface
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Returns runner run time
     *
     * @return  integer seconds
     */
    public function getTime()
    {
        return $this->finishTime - $this->startTime;
    }

    /**
     * @see Everzet\Behat\Runner\RunnerInterface
     */
    public function run()
    {
        $this->fireEvent('test.before');
        $this->startTime = microtime(true);

        $status = $this->doRun();

        $this->finishTime = microtime(true);
        $this->fireEvent('test.after');

        return $this->statusCode = $status;
    }

    /**
     * Converts string status representation to status code
     *
     * @param   string  $status string status representation (passed/failed/skipped etc.)
     * 
     * @return  integer         status code
     */
    protected function statusToCode($status)
    {
        return array_search($status, self::$statuses);
    }

    /**
     * Converts status code to string representation
     *
     * @param   integer $code   status code
     * 
     * @return  string          string status
     */
    protected function codeToStatus($code)
    {
        return self::$statuses[$code];
    }

    /**
     * Fires custom event, bounded to current runner
     *
     * @param   string  $eventName  custom event name
     */
    protected function fireEvent($eventName)
    {
        $this->dispatcher->notify(new Event($this, $this->runnerName . '.' . $eventName));
    }

    /**
     * Runs steps test runners
     *
     * @return  integer     result status code (@see BaseRunner)
     */
    abstract protected function doRun();
}
