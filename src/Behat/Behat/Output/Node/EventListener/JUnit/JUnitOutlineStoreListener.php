<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\JUnit;

use Behat\Behat\Output\Node\Printer\SuitePrinter;
use Behat\Testwork\Event\Event;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

/**
 * Listens for Outline events store the current one.
 *
 * @author James Watson <james@sitepulse.org>
 */
final class JUnitOutlineStoreListener implements EventListener
{
    /**
     * Initializes listener.
     */
    public function __construct(
        private readonly SuitePrinter $suitePrinter,
    ) {
    }

    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->printHeaderOnBeforeSuiteTestedEvent($formatter, $event);
        $this->printFooterOnAfterSuiteTestedEvent($formatter, $event);
    }

    private function printHeaderOnBeforeSuiteTestedEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof BeforeSuiteTested) {
            return;
        }
        $this->suitePrinter->printHeader($formatter, $event->getSuite());
    }

    private function printFooterOnAfterSuiteTestedEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof AfterSuiteTested) {
            return;
        }
        $this->suitePrinter->printFooter($formatter, $event->getSuite());
    }
}
