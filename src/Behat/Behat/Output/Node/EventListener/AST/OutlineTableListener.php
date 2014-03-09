<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\AST;

use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\Output\Node\Printer\ExampleRowPrinter;
use Behat\Behat\Output\Node\Printer\OutlineTablePrinter;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Behat outline table listener.
 *
 * Listens to outline table events and calls appropriate printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineTableListener implements EventListener
{
    /**
     * @var OutlineTablePrinter
     */
    private $tablePrinter;
    /**
     * @var ExampleRowPrinter
     */
    private $exampleRowPrinter;

    /**
     * @var OutlineNode
     */
    private $outline;
    /**
     * @var Boolean
     */
    private $headerPrinted = false;
    /**
     * @var StepTested[]
     */
    private $stepTestedEvents = array();

    /**
     * Initializes listener.
     *
     * @param OutlineTablePrinter $tablePrinter
     * @param ExampleRowPrinter   $exampleRowPrinter
     */
    public function __construct(OutlineTablePrinter $tablePrinter, ExampleRowPrinter $exampleRowPrinter)
    {
        $this->tablePrinter = $tablePrinter;
        $this->exampleRowPrinter = $exampleRowPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if ($event instanceof StepTested && StepTested::AFTER === $eventName) {
            $this->captureStepEvent($event);

            return;
        }

        $this->captureOutlineOnBeforeOutlineEvent($event);
        $this->forgetOutlineOnAfterOutlineEvent($eventName);

        $this->printHeaderOnAfterExampleEvent($formatter, $event, $eventName);
        $this->printExampleRowOnAfterExampleEvent($formatter, $event, $eventName);
        $this->printFooterOnAfterEvent($formatter, $event);
    }

    /**
     * Captures step tested event.
     *
     * @param StepTested $event
     */
    private function captureStepEvent(StepTested $event)
    {
        $this->stepTestedEvents[$event->getStep()->getLine()] = $event;
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

        $this->outline = $event->getOutline();
        $this->headerPrinted = false;
    }

    /**
     * Removes outline from the ivar on outline AFTER event.
     *
     * @param $eventName
     */
    private function forgetOutlineOnAfterOutlineEvent($eventName)
    {
        if (OutlineTested::AFTER !== $eventName) {
            return;
        }

        $this->outline = null;
    }

    /**
     * Prints outline header (if has not been printed yet) on example AFTER event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     * @param string    $eventName
     */
    private function printHeaderOnAfterExampleEvent(Formatter $formatter, Event $event, $eventName)
    {
        if (!$event instanceof AfterScenarioTested || ExampleTested::AFTER !== $eventName) {
            return;
        }

        if ($this->headerPrinted) {
            return;
        }

        $feature = $event->getFeature();
        $stepTestResults = $this->getStepTestResults();

        $this->tablePrinter->printHeader($formatter, $feature, $this->outline, $stepTestResults);
        $this->headerPrinted = true;
    }

    /**
     * Prints example row on example AFTER event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     * @param string    $eventName
     */
    private function printExampleRowOnAfterExampleEvent(Formatter $formatter, Event $event, $eventName)
    {
        if (!$event instanceof AfterScenarioTested || ExampleTested::AFTER !== $eventName) {
            return;
        }

        $example = $event->getScenario();

        $this->exampleRowPrinter->printExampleRow($formatter, $this->outline, $example, $this->stepTestedEvents);
        $this->stepTestedEvents = array();
    }

    /**
     * Prints outline footer on outline AFTER event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     */
    private function printFooterOnAfterEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof AfterOutlineTested) {
            return;
        }

        $feature = $event->getFeature();
        $outline = $event->getOutline();
        $result = $event->getTestResult();

        $this->tablePrinter->printFooter($formatter, $feature, $outline, $result);
    }

    /**
     * Returns currently captured step events results.
     *
     * @return StepResult[]
     */
    private function getStepTestResults()
    {
        return array_map(
            function (AfterStepTested $event) {
                return $event->getTestResult();
            },
            $this->stepTestedEvents
        );
    }
}
