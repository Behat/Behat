<?php

namespace Behat\Behat\Hook\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\Event\ExecuteCalleeEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\LifecycleEventInterface;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Hook\Event\HooksCarrierEvent;
use Behat\Behat\Hook\HookInterface;
use Exception;
use ReflectionObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Hook dispatcher.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookDispatcher extends DispatchingService implements EventSubscriberInterface
{
    /**
     * @var Boolean
     */
    private $skip = false;

    /**
     * Initializes dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Boolean                  $skip
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $skip = false)
    {
        parent::__construct($eventDispatcher);

        $this->skip = (bool)$skip;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::BEFORE_SUITE           => array('dispatchHooks', 10),
            EventInterface::AFTER_SUITE            => array('dispatchHooks', 10),
            EventInterface::BEFORE_FEATURE         => array('dispatchHooks', 10),
            EventInterface::AFTER_FEATURE          => array('dispatchHooks', 10),
            EventInterface::BEFORE_SCENARIO        => array('dispatchHooks', 10),
            EventInterface::AFTER_SCENARIO         => array('dispatchHooks', 10),
            EventInterface::BEFORE_OUTLINE_EXAMPLE => array('dispatchHooks', 10),
            EventInterface::AFTER_OUTLINE_EXAMPLE  => array('dispatchHooks', 10),
            EventInterface::BEFORE_STEP            => array('dispatchHooks', 10),
            EventInterface::AFTER_STEP             => array('dispatchHooks', 10),
        );
    }

    /**
     * Tells dispatcher to skip all hooks.
     *
     * @param Boolean $skip
     */
    public function skipHooks($skip = true)
    {
        $this->skip = (bool)$skip;
    }

    /**
     * Runs hooks with specified name.
     *
     * @param LifecycleEventInterface $event An event to which hooks glued
     *
     * @throws Exception If hook throws one
     */
    public function dispatchHooks(LifecycleEventInterface $event)
    {
        if ($this->skip) {
            return;
        }

        $hooksProvider = new HooksCarrierEvent($event->getSuite(), $event->getContextPool());
        $this->dispatch(EventInterface::LOAD_HOOKS, $hooksProvider);

        foreach ($hooksProvider->getHooksForEvent($event) as $hook) {
            $execution = new ExecuteCalleeEvent(
                $event->getSuite(), $event->getContextPool(), $hook, array($event)
            );

            try {
                $this->dispatch(EventInterface::EXECUTE_HOOK, $execution);
            } catch (Exception $e) {
                $this->addHookInformationToException($hook, $e);

                throw $e;
            }
        }
    }

    /**
     * Adds a hook information to exception thrown from it.
     *
     * @param HookInterface $hook      hook instance
     * @param Exception     $exception exception
     */
    private function addHookInformationToException(HookInterface $hook, Exception $exception)
    {
        $reflection = new ReflectionObject($exception);
        $message = $reflection->getProperty('message');

        $message->setAccessible(true);
        $message->setValue(
            $exception,
            sprintf(
                "Exception has been thrown in '%s' hook, defined in %s\n\n%s",
                $hook->getEventName(),
                $hook->getPath(),
                $exception->getMessage()
            )
        );
    }
}
