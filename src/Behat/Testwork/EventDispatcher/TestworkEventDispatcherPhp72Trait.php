<?php

/**
 * This trait is created to allow us to have PHP7.2 code in this project and still
 * be able to support older PHP versions.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
trait TestworkEventDispatcherPhp72Trait
{
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
}