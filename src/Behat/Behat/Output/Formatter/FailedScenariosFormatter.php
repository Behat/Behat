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
use Behat\Behat\Event\ExampleEvent;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;

/**
 * Failed scenarios formatter.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FailedScenariosFormatter extends CliFormatter
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::AFTER_SCENARIO => array('afterScenario', -50),
            EventInterface::AFTER_EXAMPLE  => array('afterExample', -50),
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
     * Listens to AFTER_SCENARIO event.
     *
     * @param ScenarioEvent $event
     */
    public function afterScenario(ScenarioEvent $event)
    {
        if (StepEvent::FAILED === $event->getStatus()) {
            $scenario = $event->getScenario();
            $this->writeln($scenario->getFile() . ':' . $scenario->getLine());
        }
    }

    /**
     * Listens to AFTER_EXAMPLE event.
     *
     * @param ExampleEvent $event
     */
    public function afterExample(ExampleEvent $event)
    {
        if (StepEvent::FAILED === $event->getStatus()) {
            $example = $event->getExample();
            $this->writeln($example->getFile() . ':' . $example->getLine());
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
