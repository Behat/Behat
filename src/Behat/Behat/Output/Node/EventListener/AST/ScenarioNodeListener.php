<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\AST;

use Behat\Behat\EventDispatcher\Event\ScenarioLikeTested;
use Behat\Behat\Output\Node\Printer\ScenarioPrinter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Behat scenario node listener.
 *
 * Listens to scenario events and calls appropriate printers (header/footer).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioNodeListener implements EventListener
{
    /**
     * @var string
     */
    private $eventClass;
    /**
     * @var ScenarioPrinter
     */
    private $scenarioPrinter;

    /**
     * Initializes listener.
     *
     * @param string          $eventClass
     * @param ScenarioPrinter $scenarioPrinter
     */
    public function __construct($eventClass, ScenarioPrinter $scenarioPrinter)
    {
        $this->eventClass = $eventClass;
        $this->scenarioPrinter = $scenarioPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if (!$this->isSubclassOEventClass($event)) {
            return;
        }

        $this->printHeaderOnBeforeEvent($formatter, $event, $eventName);
        $this->printFooterOnAfterEvent($formatter, $event, $eventName);
    }

    /**
     * Checks if provided event is a subclass of expected scenario event.
     *
     * @param Event $event
     *
     * @return Boolean
     */
    private function isSubclassOEventClass(Event $event)
    {
        return $this->eventClass === get_class($event) || is_subclass_of($event, $this->eventClass);
    }

    /**
     * Prints scenario/background header on BEFORE event.
     *
     * @param Formatter          $formatter
     * @param ScenarioLikeTested $event
     * @param string             $eventName
     */
    private function printHeaderOnBeforeEvent(Formatter $formatter, ScenarioLikeTested $event, $eventName)
    {
        $class = $this->eventClass;
        if ($class::BEFORE !== $eventName) {
            return;
        }

        $this->scenarioPrinter->printHeader($formatter, $event->getFeature(), $event->getScenario());
    }

    /**
     * Prints scenario/background footer on AFTER event.
     *
     * @param Formatter          $formatter
     * @param ScenarioLikeTested $event
     * @param string             $eventName
     */
    private function printFooterOnAfterEvent(Formatter $formatter, ScenarioLikeTested $event, $eventName)
    {
        $class = $this->eventClass;
        if ($class::AFTER !== $eventName) {
            return;
        }

        $feature = $event->getFeature();
        $scenario = $event->getScenario();
        $result = $event->getTestResult();

        $this->scenarioPrinter->printFooter($formatter, $feature, $scenario, $result);
    }
}
