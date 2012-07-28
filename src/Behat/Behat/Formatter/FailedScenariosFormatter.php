<?php

namespace Behat\Behat\Formatter;

use Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\OutlineExampleEvent,
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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FailedScenariosFormatter extends ConsoleFormatter
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultParameters()
    {
        return array();
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        $events = array('afterScenario', 'afterOutlineExample');

        return array_combine($events, $events);
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param ScenarioEvent $event
     */
    public function afterScenario(ScenarioEvent $event)
    {
        if (StepEvent::FAILED === $event->getResult()) {
            $scenario = $event->getScenario();
            $this->writeln($scenario->getFile().':'.$scenario->getLine());
        }
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param ScenarioEvent $event
     */
    public function afterOutlineExample(OutlineExampleEvent $event)
    {
        if (StepEvent::FAILED === $event->getResult()) {
            $outline  = $event->getOutline();
            $examples = $outline->getExamples();
            $lines    = $examples->getRowLines();
            $this->writeln($outline->getFile().':'.$lines[$event->getIteration() + 1]);
        }
    }
}
