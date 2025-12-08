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
use Behat\Testwork\Event\Event;
use Behat\Testwork\EventDispatcher\Event\AfterSetup;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

/**
 * Listens to scenario events and calls appropriate printers (header/footer).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ScenarioNodeListener implements EventListener
{
    public function __construct(
        private readonly string $beforeEventName,
        private readonly string $afterEventName,
        private readonly ScenarioPrinter $scenarioPrinter,
        private readonly ?SetupPrinter $setupPrinter = null,
    ) {
    }

    public function listenEvent(Formatter $formatter, Event $event, $eventName): void
    {
        if (!$event instanceof ScenarioLikeTested) {
            return;
        }

        $this->printHeaderOnBeforeEvent($formatter, $event, $eventName);
        $this->printFooterOnAfterEvent($formatter, $event, $eventName);
    }

    /**
     * Prints scenario/background header on BEFORE event.
     */
    private function printHeaderOnBeforeEvent(Formatter $formatter, ScenarioLikeTested $event, string $eventName): void
    {
        if ($this->beforeEventName !== $eventName || !$event instanceof AfterSetup) {
            return;
        }

        if ($this->setupPrinter instanceof SetupPrinter) {
            $this->setupPrinter->printSetup($formatter, $event->getSetup());
        }

        $this->scenarioPrinter->printHeader($formatter, $event->getFeature(), $event->getScenario());
    }

    /**
     * Prints scenario/background footer on AFTER event.
     */
    private function printFooterOnAfterEvent(Formatter $formatter, ScenarioLikeTested $event, string $eventName): void
    {
        if ($this->afterEventName !== $eventName || !$event instanceof AfterTested) {
            return;
        }

        if ($this->setupPrinter instanceof SetupPrinter) {
            $this->setupPrinter->printTeardown($formatter, $event->getTeardown());
        }

        $this->scenarioPrinter->printFooter($formatter, $event->getTestResult());
    }
}
