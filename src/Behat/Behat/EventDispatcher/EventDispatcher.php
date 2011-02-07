<?php

namespace Behat\Behat\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher,
    Symfony\Component\DependencyInjection\ContainerInterface;

use Behat\Behat\Formatter\FormatterInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Event dispatcher.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatcher extends BaseEventDispatcher
{
    /**
     * Run registerListeners on all container services with "behat.events_listener" tag.
     *
     * @param   ContainerInterface  $container  dependency container
     */
    public function bindContainerEventListeners(ContainerInterface $container)
    {
        foreach ($container->findTaggedServiceIds('behat.events_listener') as $id => $tag) {
            $container->get($id)->registerListeners($this);
        }
    }

    /**
     * Register formatter event listeners.
     *
     * @param   FormatterInterface  $formatter  Behat output formatter
     */
    public function bindFormatterEventListeners(FormatterInterface $formatter)
    {
        $formatter->registerListeners($this);
    }
}
