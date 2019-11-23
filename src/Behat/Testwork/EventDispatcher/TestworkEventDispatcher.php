<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Extends Symfony2 event dispatcher with catch-all listeners.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TestworkEventDispatcher extends EventDispatcher
{
    const BEFORE_ALL_EVENTS = '*~';
    const AFTER_ALL_EVENTS = '~*';

    /**
     * {@inheritdoc}
     */
    public function dispatch(object $event, string $eventName = null): object
    {
        if (null === $event) {
            $event = new Event();
        }

        if (method_exists($event, 'setName')) {
            $event->setName($eventName);
        }

        $this->callListeners($this->getListeners($eventName), $eventName, $event);

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners(string $eventName = null)
    {
        if (null == $eventName || self::BEFORE_ALL_EVENTS === $eventName) {
            return parent::getListeners($eventName);
        }

        return array_merge(
            parent::getListeners(self::BEFORE_ALL_EVENTS),
            parent::getListeners($eventName),
            parent::getListeners(self::AFTER_ALL_EVENTS)
        );
    }
}
