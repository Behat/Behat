<?php

namespace Behat\Behat\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
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

    public function registerFormatter($formatter)
    {
        $this->connect('suite.run.before',        array($formatter, 'beforeSuite'));
        $this->connect('suite.run.after',         array($formatter, 'afterSuite'));
        $this->connect('feature.run.before',      array($formatter, 'beforeFeature'));
        $this->connect('feature.run.after',       array($formatter, 'afterFeature'));
        $this->connect('background.run.before',   array($formatter, 'beforeBackground'));
        $this->connect('background.run.after',    array($formatter, 'afterBackground'));
        $this->connect('outline.run.before',      array($formatter, 'beforeOutline'));
        $this->connect('outline.sub.run.before',  array($formatter, 'beforeOutlineExample'));
        $this->connect('outline.sub.run.after',   array($formatter, 'afterOutlineExample'));
        $this->connect('outline.run.after',       array($formatter, 'afterOutline'));
        $this->connect('scenario.run.before',     array($formatter, 'beforeScenario'));
        $this->connect('scenario.run.after',      array($formatter, 'afterScenario'));
        $this->connect('step.run.before',         array($formatter, 'beforeStep'));
        $this->connect('step.run.after',          array($formatter, 'afterStep'));
    }
}
