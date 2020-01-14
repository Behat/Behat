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

/**
 * Extends Symfony2 event dispatcher with catch-all listeners.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */

if (class_exists(\Symfony\Contracts\EventDispatcher\Event::class) && PHP_VERSION_ID > 70200) {
    // Assert: This is Symfony 5 and PHP >= 7.2
    include_once __DIR__.'/TestworkEventDispatcherPhp72Trait.php';

    final class TestworkEventDispatcher extends EventDispatcher
    {
        use \TestworkEventDispatcherPhp72Trait;
        const BEFORE_ALL_EVENTS = '*~';
        const AFTER_ALL_EVENTS = '~*';
        const DISPATCHER_VERSION = 2;

        /**
         * {@inheritdoc}
         */
        public function getListeners($eventName = null)
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
} else {

    final class TestworkEventDispatcher extends EventDispatcher
    {
        const BEFORE_ALL_EVENTS = '*~';
        const AFTER_ALL_EVENTS = '~*';
        const DISPATCHER_VERSION = 1;

        /**
         * {@inheritdoc}
         */
        public function dispatch($eventName, \Symfony\Component\EventDispatcher\Event $event = null)
        {
            if (null === $event) {
                $event = new \Symfony\Component\EventDispatcher\Event();
            }
            if (method_exists($event, 'setName')) {
                $event->setName($eventName);
            }
            /** @scrutinizer ignore-call */
            $this->doDispatch($this->getListeners($eventName), $eventName, $event);

            return $event;
        }
        /**
         * {@inheritdoc}
         */
        public function getListeners($eventName = null)
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

}
