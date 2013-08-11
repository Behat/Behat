<?php

namespace Behat\Behat\Context\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\UninitializedContextPool;
use Behat\Behat\Context\Event\ContextPoolCarrierEvent;
use Behat\Behat\Event\EventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Context pool factory.
 * Listens to CREATE_CONTEXT_POOL event and creates a context pool.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextPoolFactory implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(EventInterface::CREATE_CONTEXT_POOL => array('createContextPool', 0));
    }

    /**
     * Creates and sets new UninitializedContextPool to the event.
     *
     * @param ContextPoolCarrierEvent $event
     *
     * @see UninitializedContextPool
     */
    public function createContextPool(ContextPoolCarrierEvent $event)
    {
        if ($event->hasContextPool()) {
            return;
        }

        $uninitializedPool = new UninitializedContextPool();
        foreach ($event->getSuite()->getContextClasses() as $class) {
            $uninitializedPool->registerContextClass($class);
        }

        $event->setContextPool($uninitializedPool);
    }
}
