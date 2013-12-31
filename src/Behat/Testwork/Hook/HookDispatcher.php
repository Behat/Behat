<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook;

use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Hook\Call\HookCall;
use Behat\Testwork\Hook\Event\LifecycleEvent;

/**
 * Testwork hook dispatcher.
 *
 * Dispatches registered hooks for provided events.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookDispatcher
{
    /**
     * @var HookRepository
     */
    private $repository;
    /**
     * @var CallCenter
     */
    private $callCenter;

    /**
     * Initializes hook dispatcher.
     *
     * @param HookRepository $repository
     * @param CallCenter     $callCenter
     */
    public function __construct(HookRepository $repository, CallCenter $callCenter)
    {
        $this->repository = $repository;
        $this->callCenter = $callCenter;
    }

    /**
     * Dispatches hooks for a specified event.
     *
     * @param string         $eventName
     * @param LifecycleEvent $event
     *
     * @return CallResults
     */
    public function dispatchEventHooks($eventName, LifecycleEvent $event)
    {
        $results = array();
        foreach ($this->repository->getEventHooks($eventName, $event) as $hook) {
            $results[] = $this->dispatchEventHook($event, $hook);
        }

        return new CallResults($results);
    }

    /**
     * Dispatches single event hook.
     *
     * @param LifecycleEvent $event
     * @param Hook           $hook
     *
     * @return CallResult
     */
    private function dispatchEventHook(LifecycleEvent $event, Hook $hook)
    {
        return $this->callCenter->makeCall(new HookCall($event, $hook));
    }
}
