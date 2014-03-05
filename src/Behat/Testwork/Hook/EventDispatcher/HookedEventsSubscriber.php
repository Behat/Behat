<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\EventDispatcher;

use Behat\Testwork\Call\CallResults;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Hook\EventDispatcher\Event\HookDispatched;
use Behat\Testwork\Hook\HookDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Testwork hooked events subscriber.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookedEventsSubscriber implements EventSubscriberInterface
{
    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes subscriber.
     *
     * @param HookDispatcher           $hookDispatcher
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(HookDispatcher $hookDispatcher, EventDispatcherInterface $eventDispatcher)
    {
        $this->hookDispatcher = $hookDispatcher;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Returns array of event names that are hookable.
     *
     * @return array
     */
    public static function getHookableEvents()
    {
        return array(
            SuiteTested::BEFORE,
            SuiteTested::AFTER,
        );
    }

    /**
     * {@inheritdoc}
     */
    final public static function getSubscribedEvents()
    {
        return array_combine(
            static::getHookableEvents(),
            array_fill(0, count(static::getHookableEvents()), array('dispatchHooksForEvent', -9999))
        );
    }

    /**
     * Dispatches hooks for a provided event.
     *
     * @param LifecycleEvent $event
     * @param string         $eventName
     */
    public function dispatchHooksForEvent(LifecycleEvent $event, $eventName)
    {
        $this->eventDispatcher->dispatch(HookDispatched::BEFORE, new HookDispatched($eventName, $event));
        $callResults = $this->hookDispatcher->dispatchEventHooks($eventName, $event);
        $this->eventDispatcher->dispatch(HookDispatched::AFTER, new HookDispatched($eventName, $event, $callResults));

        $this->rethrowException($callResults);
    }

    /**
     * Rethrows exception from provided call results.
     *
     * @param CallResults $callResults
     */
    private function rethrowException(CallResults $callResults)
    {
        foreach ($callResults as $callResult) {
            if ($callResult->hasException()) {
                throw $callResult->getException();
            }
        }
    }
}
