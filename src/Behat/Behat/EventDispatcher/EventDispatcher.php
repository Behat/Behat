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
        $this->connect('suite.before',              array($formatter, 'beforeSuite'),               -10);
        $this->connect('suite.after',               array($formatter, 'afterSuite'),                -10);
        $this->connect('feature.before',            array($formatter, 'beforeFeature'),             -10);
        $this->connect('feature.after',             array($formatter, 'afterFeature'),              -10);
        $this->connect('background.before',         array($formatter, 'beforeBackground'),          -10);
        $this->connect('background.after',          array($formatter, 'afterBackground'),           -10);
        $this->connect('outline.before',            array($formatter, 'beforeOutline'),             -10);
        $this->connect('outline.example.before',    array($formatter, 'beforeOutlineExample'),      -10);
        $this->connect('outline.example.after',     array($formatter, 'afterOutlineExample'),       -10);
        $this->connect('outline.after',             array($formatter, 'afterOutline'),              -10);
        $this->connect('scenario.before',           array($formatter, 'beforeScenario'),            -10);
        $this->connect('scenario.after',            array($formatter, 'afterScenario'),             -10);
        $this->connect('step.before',               array($formatter, 'beforeStep'),                -10);
        $this->connect('step.after',                array($formatter, 'afterStep'),                 -10);
    }
}
