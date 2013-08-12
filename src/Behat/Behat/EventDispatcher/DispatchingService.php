<?php

namespace Behat\Behat\EventDispatcher;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract dispatching service.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class DispatchingService
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes service.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Dispatches an event.
     *
     * @param string         $eventName
     * @param EventInterface $event
     */
    protected function dispatch($eventName, EventInterface $event)
    {
        $this->eventDispatcher->dispatch($eventName, $event);
    }
}
