<?php

namespace Behat\Behat\Hook\UseCase;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Reader\CalleesReader;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Hook\Event\HooksCarrierEvent;
use Behat\Behat\Hook\HookInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Context hooks loader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class LoadContextHooks implements EventSubscriberInterface
{
    /**
     * @var CalleesReader
     */
    private $calleesReader;

    /**
     * Initializes reader.
     *
     * @param CalleesReader $calleesReader
     */
    public function __construct(CalleesReader $calleesReader)
    {
        $this->calleesReader = $calleesReader;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(EventInterface::LOAD_HOOKS => array('loadHooks', 0));
    }

    /**
     * Loads contexts hooks into event.
     *
     * @param HooksCarrierEvent $event
     */
    public function loadHooks(HooksCarrierEvent $event)
    {
        foreach ($this->calleesReader->read($event->getSuite(), $event->getContextPool()) as $callback) {
            if ($callback instanceof HookInterface) {
                $event->addHook($callback);
            }
        }
    }
}
