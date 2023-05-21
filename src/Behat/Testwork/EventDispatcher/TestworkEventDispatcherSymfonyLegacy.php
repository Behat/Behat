<?php


namespace Behat\Testwork\EventDispatcher;


use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Extends Symfony2 ( < 5.0) event dispatcher with catch-all listeners.
 *
 * This is magically aliased to TestworkEventDispatcher by the code in TestworkEventDispatcher.php
 * if the old symfony interface is detected.
 *
 * @deprecated Do not reference this class directly, use TestworkEventDispatcher
 */
final class TestworkEventDispatcherSymfonyLegacy extends EventDispatcher
{
    public const BEFORE_ALL_EVENTS = '*~';
    public const AFTER_ALL_EVENTS = '~*';
    public const DISPATCHER_VERSION = 1;

    /**
     * {@inheritdoc}
     *
     */
    public function dispatch($eventName, Event $event = null)
    {
        trigger_error(
            'Class "\Behat\Testwork\EventDispatcher\TestworkEventDispatcherSymfonyLegacy" is deprecated ' .
            'and should not be relied upon anymore. Use "Behat\Testwork\EventDispatcher\TestworkEventDispatcher" ' .
            'instead',
            E_USER_DEPRECATED
        );

        if (null === $event) {
            /** @psalm-suppress UndefinedClass */
            $event = new Event();
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
        trigger_error(
            'Class "\Behat\Testwork\EventDispatcher\TestworkEventDispatcherSymfonyLegacy" is deprecated ' .
            'and should not be relied upon anymore. Use "Behat\Testwork\EventDispatcher\TestworkEventDispatcher" ' .
            'instead',
            E_USER_DEPRECATED
        );

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
