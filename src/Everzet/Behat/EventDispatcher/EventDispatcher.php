<?php

namespace Everzet\Behat\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventDispatcher extends BaseEventDispatcher
{
    public function __construct(ContainerInterface $container)
    {
        foreach ($container->findTaggedServiceIds('events_listener') as $id) {
            $container->get($id)->registerListeners($this);
        }
    }
}
