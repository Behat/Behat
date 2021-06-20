<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Extends Symfony2 event dispatcher with catch-all listeners.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TestworkEventDispatcher extends EventDispatcher
{
    public const BEFORE_ALL_EVENTS = '*~';
    public const AFTER_ALL_EVENTS = '~*';
    public const DISPATCHER_VERSION = 2;

    /**
     * {@inheritdoc}
     *
     * @param string|null $eventName
     */
    public function getListeners($eventName = null): array
    {
        if (null === $eventName || self::BEFORE_ALL_EVENTS === $eventName) {
            return parent::getListeners($eventName);
        }

        return array_merge(
            parent::getListeners(self::BEFORE_ALL_EVENTS),
            parent::getListeners($eventName),
            parent::getListeners(self::AFTER_ALL_EVENTS)
        );
    }

    public function dispatch($event, $eventName = null): object
    {
        if (is_object($event)) {
            return $this->bcAwareDispatch($event, $eventName);
        }

        return $this->bcAwareDispatch($eventName, $event);
    }

    private function bcAwareDispatch(object $event, $eventName)
    {
        if (null === $event) {
            $event = new Event();
        }

        if (method_exists($event, 'setName')) {
            $event->setName($eventName);
        }

        return parent::dispatch($event, $eventName);
    }
}
