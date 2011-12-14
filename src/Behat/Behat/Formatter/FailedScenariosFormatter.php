<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\OutlineEvent,
    Behat\Behat\Event\StepEvent;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Failed scenarios formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FailedScenariosFormatter extends ConsoleFormatter
{
    /**
     * {@inheritdoc}
     */
    public static function getDescription()
    {
        return "Prints list of failed scenarios.";
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultParameters()
    {
        return array();
    }

    /**
     * @see     Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */
    public static function getSubscribedEvents()
    {
        $events = array('afterScenario', 'afterOutline');

        return array_combine($events, $events);
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param   Behat\Behat\Event\ScenarioEvent     $event
     */
    public function afterScenario(ScenarioEvent $event)
    {
        if (StepEvent::FAILED === $event->getResult()) {
            $scenario = $event->getScenario();
            $this->writeln($scenario->getFile().':'.$scenario->getLine());
        }
    }

    /**
     * Listens to "outline.after" event.
     *
     * @param   Behat\Behat\Event\ScenarioEvent     $event
     */
    public function afterOutline(OutlineEvent $event)
    {
        if (StepEvent::FAILED === $event->getResult()) {
            $outline = $event->getOutline();
            $this->writeln($outline->getFile().':'.$outline->getLine());
        }
    }
}
