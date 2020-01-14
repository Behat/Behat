<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\JUnit;

use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\Output\Node\Printer\SuitePrinter;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Event\Event;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

/**
 * Listens for Outline events store the current one
 *
 * @author James Watson <james@sitepulse.org>
 */
final class JUnitOutlineStoreListener implements EventListener
{

    /**
     * @var SuitePrinter
     */
    private $suitePrinter;

    /**
     * @var array
     */
    private $lineScenarioMap = array();

    /**
     * Initializes listener.
     *
     * @param SuitePrinter $suitePrinter
     */
    public function __construct(SuitePrinter $suitePrinter)
    {
        $this->suitePrinter = $suitePrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->captureOutlineOnBeforeOutlineEvent($event);

        $this->printHeaderOnBeforeSuiteTestedEvent($formatter, $event);
        $this->printFooterOnAfterSuiteTestedEvent($formatter, $event);
    }

    /**
     * Captures outline into the ivar on outline BEFORE event.
     *
     * @param Event $event
     */
    private function captureOutlineOnBeforeOutlineEvent(Event $event)
    {
        if (!$event instanceof BeforeOutlineTested) {
            return;
        }

        $outline = $event->getOutline();
        foreach ($outline->getExamples() as $example) {
            $this->lineScenarioMap[$example->getLine()] = $outline;
        }
    }

    /**
     * @param Formatter $formatter
     * @param Event     $event
     */
    private function printHeaderOnBeforeSuiteTestedEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof BeforeSuiteTested) {
            return;
        }
        $this->suitePrinter->printHeader($formatter, $event->getSuite());
    }

    /**
     * @param Formatter $formatter
     * @param Event     $event
     */
    private function printFooterOnAfterSuiteTestedEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof AfterSuiteTested) {
            return;
        }
        $this->suitePrinter->printFooter($formatter, $event->getSuite());
    }

    /**
     * @param ExampleNode $scenario
     * @return OutlineNode
     */
    public function getCurrentOutline(ExampleNode $scenario)
    {
        return $this->lineScenarioMap[$scenario->getLine()];
    }
}
