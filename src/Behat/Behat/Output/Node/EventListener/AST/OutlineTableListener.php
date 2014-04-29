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
use Behat\Behat\EventDispatcher\Event\AfterScenarioSetup;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepSetup;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\Output\Node\Printer\ExampleRowPrinter;
use Behat\Behat\Output\Node\Printer\OutlineTablePrinter;
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Behat\Testwork\Tester\Setup\Setup;
use Symfony\Component\EventDispatcher\Event;

/**
 * Listens to outline table events and calls appropriate printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class OutlineTableListener implements EventListener
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
     * @var SetupPrinter
     */
    private $stepSetupPrinter;
    /**
     * @var SetupPrinter
     */
    private $exampleSetupPrinter;
    /**
     * @var OutlineNode
     */
    private $outline;
    /**
     * @var Setup
     */
    private $exampleSetup;
    /**
     * @var Boolean
     */
    private $headerPrinted = false;
    /**
     * @var AfterStepSetup[]
     */
    private $stepBeforeTestedEvents = array();
    /**
     * @var AfterStepTested[]
     */
    private $stepAfterTestedEvents = array();

    /**
     * Initializes listener.
     *
     * @param OutlineTablePrinter $tablePrinter
     * @param ExampleRowPrinter   $exampleRowPrinter
     * @param SetupPrinter        $exampleSetupPrinter
     * @param SetupPrinter        $stepSetupPrinter
     */
    public function __construct(
        OutlineTablePrinter $tablePrinter,
        ExampleRowPrinter $exampleRowPrinter,
        SetupPrinter $exampleSetupPrinter,
        SetupPrinter $stepSetupPrinter
    ) {
        $this->tablePrinter = $tablePrinter;
        $this->exampleRowPrinter = $exampleRowPrinter;
        $this->exampleSetupPrinter = $exampleSetupPrinter;
        $this->stepSetupPrinter = $stepSetupPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if ($event instanceof StepTested) {
            $this->captureStepEvent($event);

            return;
        }

        $this->captureOutlineOnBeforeOutlineEvent($event);
        $this->forgetOutlineOnAfterOutlineEvent($eventName);
        $this->captureExampleSetupOnBeforeEvent($event);

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
        if ($event instanceof AfterStepSetup) {
            $this->stepBeforeTestedEvents[$event->getStep()->getLine()] = $event;
        } else {
            $this->stepAfterTestedEvents[$event->getStep()->getLine()] = $event;
        }
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
     * Captures example setup on example BEFORE event.
     *
     * @param Event $event
     */
    private function captureExampleSetupOnBeforeEvent(Event $event)
    {
        if (!$event instanceof AfterScenarioSetup) {
            return;
        }

        $this->exampleSetup = $event->getSetup();
    }

    /**
     * Removes outline from the ivar on outline AFTER event.
     *
     * @param string $eventName
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

        $this->exampleSetupPrinter->printSetup($formatter, $this->exampleSetup);

        foreach ($this->stepBeforeTestedEvents as $beforeEvent) {
            $this->stepSetupPrinter->printSetup($formatter, $beforeEvent->getSetup());
        }

        $this->exampleRowPrinter->printExampleRow($formatter, $this->outline, $example, $this->stepAfterTestedEvents);

        foreach ($this->stepAfterTestedEvents as $afterEvent) {
            $this->stepSetupPrinter->printTeardown($formatter, $afterEvent->getTeardown());
        }

        $this->exampleSetupPrinter->printTeardown($formatter, $event->getTeardown());

        $this->exampleSetup = null;
        $this->stepBeforeTestedEvents = array();
        $this->stepAfterTestedEvents = array();
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

        $this->tablePrinter->printFooter($formatter, $event->getTestResult());
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
            $this->stepAfterTestedEvents
        );
    }
}
