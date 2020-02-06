<?php


namespace Behat\Testwork\EventDispatcher;


use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Extends Symfony2 (>=5.0) event dispatcher with catch-all listeners.
 *
 * This is magically aliased to TestworkEventDispatcher by the code in TestworkEventDispatcher.php
 * if the new symfony interface is detected.
 *
 * @deprecated Do not reference this class directly, use TestworkEventDispatcher
 */
final class TestworkEventDispatcherSymfony5 extends EventDispatcher
{
    const BEFORE_ALL_EVENTS = '*~';
    const AFTER_ALL_EVENTS = '~*';
    const DISPATCHER_VERSION = 2;

    /**
     * {@inheritdoc}
     */
    public function dispatch($event, string $eventName = null): object
    {
        if (null === $event) {
            $event = new \Symfony\Contracts\EventDispatcher\Event();
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
