<?php

namespace Behat\Behat\Output\Formatter;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\OutlineExampleEvent;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Output\Formatter\ConsoleFormatter;

/**
 * Failed scenarios formatter.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FailedScenariosFormatter extends ConsoleFormatter
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::AFTER_SCENARIO        => array('afterScenario', -50),
            EventInterface::AFTER_OUTLINE_EXAMPLE => array('afterOutlineExample', -50),
        );
    }

    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return 'failed';
    }

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Prints list of failed scenarios.';
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
            $this->writeln($scenario->getFile() . ':' . $scenario->getLine());
        }
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param OutlineExampleEvent $event
     */
    public function afterOutlineExample(OutlineExampleEvent $event)
    {
        if (StepEvent::FAILED === $event->getResult()) {
            $outline = $event->getOutline();
            $examples = $outline->getExamples();
            $lines = $examples->getRowLines();
            $this->writeln($outline->getFile() . ':' . $lines[$event->getIteration() + 1]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultParameters()
    {
        return array();
    }
}
