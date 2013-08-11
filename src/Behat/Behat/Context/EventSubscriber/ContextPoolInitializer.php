<?php

namespace Behat\Behat\Context\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\InitializedContextPool;
use Behat\Behat\Context\Initializer\InitializerInterface;
use Behat\Behat\Context\Pool\UninitializedContextPool;
use Behat\Behat\Context\Event\ContextPoolCarrierEvent;
use Behat\Behat\Event\EventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Context pool initializer.
 * Listens to INITIALIZE_CONTEXT_POOL event and transforms uninitialized context pool into an initialized one.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextPoolInitializer implements EventSubscriberInterface
{
    /**
     * @var InitializerInterface[]
     */
    private $initializers = array();

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(EventInterface::INITIALIZE_CONTEXT_POOL => array('initializeContextPool', 0));
    }

    /**
     * Registers context initializer.
     *
     * @param InitializerInterface $initializer
     */
    public function registerInitializer(InitializerInterface $initializer)
    {
        $this->initializers[] = $initializer;
    }

    /**
     * Creates and sets initialized context pool out of event's uninitialized one.
     * Uses registered context initializers during contexts initialization phase.
     *
     * @param ContextPoolCarrierEvent $event
     *
     * @see UninitializedContextPool
     * @see InitializedContextPool
     * @see InitializerInterface
     */
    public function initializeContextPool(ContextPoolCarrierEvent $event)
    {
        if (!$event->hasContextPool()) {
            return;
        }
        if (!($event->getContextPool() instanceof UninitializedContextPool)) {
            return;
        }

        $initializedPool = new InitializedContextPool();
        foreach ($event->getContextPool()->getContexts() as $class) {
            $context = new $class($event->getSuite()->getParameters());

            foreach ($this->initializers as $initializer) {
                if ($initializer->supports($context)) {
                    $initializer->initialize($context);
                }
            }

            $initializedPool->registerContext($context);
        }

        $event->setContextPool($initializedPool);
    }
}
