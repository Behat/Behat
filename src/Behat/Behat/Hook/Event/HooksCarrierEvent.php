<?php

namespace Behat\Behat\Hook\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\LifecycleEventInterface;
use Behat\Behat\Hook\Callee\FilterableHook;
use Behat\Behat\Hook\HookInterface;
use Behat\Behat\Suite\SuiteInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Hooks carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HooksCarrierEvent extends Event implements LifecycleEventInterface
{
    /**
     * @var SuiteInterface
     */
    private $suite;
    /**
     * @var ContextPoolInterface
     */
    private $contexts;
    /**
     * @var HookInterface[]
     */
    private $hooks = array();

    /**
     * Initializes event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     */
    public function __construct(SuiteInterface $suite, ContextPoolInterface $contexts)
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
    }

    /**
     * Returns suite.
     *
     * @return SuiteInterface
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Returns context pool.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool()
    {
        return $this->contexts;
    }

    /**
     * Adds hook to the carrier.
     *
     * @param HookInterface $hook
     */
    public function addHook(HookInterface $hook)
    {
        $this->hooks[] = $hook;
    }

    /**
     * Returns all added hooks.
     *
     * @return HookInterface[]
     */
    public function getHooks()
    {
        return $this->hooks;
    }

    /**
     * Returns hooks that are related to provided event.
     *
     * @param Event $event
     *
     * @return HookInterface[]
     */
    public function getHooksForEvent(Event $event)
    {
        return array_filter(
            $this->hooks,
            function ($hook) use ($event) {
                $eventName = $event->getName();
                if (EventInterface::BEFORE_OUTLINE_EXAMPLE === $eventName) {
                    $eventName = EventInterface::BEFORE_SCENARIO;
                } elseif (EventInterface::AFTER_OUTLINE_EXAMPLE === $eventName) {
                    $eventName = EventInterface::AFTER_SCENARIO;
                }

                if ($eventName !== $hook->getEventName()) {
                    return false;
                }

                return $hook instanceof FilterableHook ? $hook->filterMatches($event) : true;
            }
        );
    }
}
