<?php

namespace Everzet\Behat\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Event dispatcher.
 * Dispatches custom Behat events to hook with.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatcher extends BaseEventDispatcher
{
    /**
     * Run registerListeners on all container services with tag 'event_listener'.
     *
     * @param   ContainerInterface  $container  dependency container
     */
    public function bindEventListeners(ContainerInterface $container)
    {
        foreach ($container->findTaggedServiceIds('behat.events_listener') as $id => $tag) {
            $container->get($id)->registerListeners($this);
        }
    }
}
