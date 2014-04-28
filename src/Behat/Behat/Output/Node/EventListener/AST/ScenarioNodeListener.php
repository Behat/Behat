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
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Testwork\EventDispatcher\Event\AfterSetup;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Listens to scenario events and calls appropriate printers (header/footer).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ScenarioNodeListener implements EventListener
{
    /**
     * @var string
     */
    private $beforeEventName;
    /**
     * @var string
     */
    private $afterEventName;
    /**
     * @var ScenarioPrinter
     */
    private $scenarioPrinter;
    /**
     * @var SetupPrinter
     */
    private $setupPrinter;

    /**
     * Initializes listener.
     *
     * @param string            $beforeEventName
     * @param string            $afterEventName
     * @param ScenarioPrinter   $scenarioPrinter
     * @param null|SetupPrinter $setupPrinter
     */
    public function __construct(
        $beforeEventName,
        $afterEventName,
        ScenarioPrinter $scenarioPrinter,
        SetupPrinter $setupPrinter = null
    ) {
        $this->beforeEventName = $beforeEventName;
        $this->afterEventName = $afterEventName;
        $this->scenarioPrinter = $scenarioPrinter;
        $this->setupPrinter = $setupPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if (!$event instanceof ScenarioLikeTested) {
            return;
        }

        $this->printHeaderOnBeforeEvent($formatter, $event, $eventName);
        $this->printFooterOnAfterEvent($formatter, $event, $eventName);
    }

    /**
     * Prints scenario/background header on BEFORE event.
     *
     * @param Formatter                     $formatter
     * @param ScenarioLikeTested|AfterSetup $event
     * @param string                        $eventName
     */
    private function printHeaderOnBeforeEvent(Formatter $formatter, ScenarioLikeTested $event, $eventName)
    {
        if ($this->beforeEventName !== $eventName || !$event instanceof AfterSetup) {
            return;
        }

        if ($this->setupPrinter) {
            $this->setupPrinter->printSetup($formatter, $event->getSetup());
        }

        $this->scenarioPrinter->printHeader($formatter, $event->getFeature(), $event->getScenario());
    }

    /**
     * Prints scenario/background footer on AFTER event.
     *
     * @param Formatter                      $formatter
     * @param ScenarioLikeTested|AfterTested $event
     * @param string                         $eventName
     */
    private function printFooterOnAfterEvent(Formatter $formatter, ScenarioLikeTested $event, $eventName)
    {
        if ($this->afterEventName !== $eventName || !$event instanceof AfterTested) {
            return;
        }

        if ($this->setupPrinter) {
            $this->setupPrinter->printTeardown($formatter, $event->getTeardown());
        }

        $this->scenarioPrinter->printFooter($formatter, $event->getTestResult());
    }
}
