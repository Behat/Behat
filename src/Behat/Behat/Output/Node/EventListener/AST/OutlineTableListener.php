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
use Behat\Behat\Output\Node\Printer\Helper\ExampleTableResolver;
use Behat\Behat\Output\Node\Printer\OutlineTablePrinter;
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Listens to outline table events and calls appropriate printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class OutlineTableListener implements EventListener
{
    private ?OutlineNode $outline = null;

    private ?Setup $exampleSetup = null;

    private bool $headerPrinted = false;

    private ?ExampleTableNode $printedExampleTable = null;

    /**
     * @var array<int,AfterStepSetup>
     */
    private array $stepBeforeTestedEvents = [];

    /**
     * @var array<int,AfterStepTested>
     */
    private array $stepAfterTestedEvents = [];

    public function __construct(
        private readonly OutlineTablePrinter $tablePrinter,
        private readonly ExampleRowPrinter $exampleRowPrinter,
        private readonly SetupPrinter $exampleSetupPrinter,
        private readonly SetupPrinter $stepSetupPrinter,
        private readonly ExampleTableResolver $exampleTableResolver,
    ) {
    }

    public function listenEvent(Formatter $formatter, Event $event, string $eventName): void
    {
        if ($event instanceof StepTested) {
            $this->captureStepEvent($event);

            return;
        }

        $this->captureOutlineOnBeforeOutlineEvent($event);
        $this->forgetOutlineOnAfterOutlineEvent($eventName);
        $this->captureExampleSetupOnBeforeEvent($event);

        $this->printExampleOnAfterExampleEvent($formatter, $event, $eventName);
        $this->printFooterOnAfterEvent($formatter, $event);
    }

    /**
     * Captures step tested event.
     */
    private function captureStepEvent(StepTested $event): void
    {
        if ($event instanceof AfterStepSetup) {
            $this->stepBeforeTestedEvents[$event->getStep()->getLine()] = $event;
        } elseif ($event instanceof AfterStepTested) {
            $this->stepAfterTestedEvents[$event->getStep()->getLine()] = $event;
        }
    }

    /**
     * Captures outline into the ivar on outline BEFORE event.
     */
    private function captureOutlineOnBeforeOutlineEvent(Event $event): void
    {
        if (!$event instanceof BeforeOutlineTested) {
            return;
        }

        $this->outline = $event->getOutline();
        $this->headerPrinted = false;
        $this->printedExampleTable = null;
    }

    /**
     * Captures example setup on example BEFORE event.
     */
    private function captureExampleSetupOnBeforeEvent(Event $event): void
    {
        if (!$event instanceof AfterScenarioSetup) {
            return;
        }

        $this->exampleSetup = $event->getSetup();
    }

    /**
     * Removes outline from the ivar on outline AFTER event.
     */
    private function forgetOutlineOnAfterOutlineEvent(string $eventName): void
    {
        if (OutlineTested::AFTER !== $eventName) {
            return;
        }

        $this->outline = null;
        $this->printedExampleTable = null;
    }

    /**
     * Prints the outline header, the examples table header and the example row on example AFTER event.
     */
    private function printExampleOnAfterExampleEvent(Formatter $formatter, Event $event, string $eventName): void
    {
        if (!$event instanceof AfterScenarioTested || ExampleTested::AFTER !== $eventName) {
            return;
        }

        $example = $event->getScenario();
        assert($example instanceof ExampleNode);

        $this->printOutlineHeaderOnce($formatter, $event);
        $exampleTable = $this->printExamplesTableHeaderOnTableChange($formatter, $example);

        $this->exampleSetupPrinter->printSetup($formatter, $this->exampleSetup);

        foreach ($this->stepBeforeTestedEvents as $beforeEvent) {
            $this->stepSetupPrinter->printSetup($formatter, $beforeEvent->getSetup());
        }

        $this->exampleRowPrinter->printExampleRow($formatter, $this->outline, $exampleTable, $example, $this->stepAfterTestedEvents);

        foreach ($this->stepAfterTestedEvents as $afterEvent) {
            $this->stepSetupPrinter->printTeardown($formatter, $afterEvent->getTeardown());
        }

        $this->exampleSetupPrinter->printTeardown($formatter, $event->getTeardown());

        $this->exampleSetup = null;
        $this->stepBeforeTestedEvents = [];
        $this->stepAfterTestedEvents = [];
    }

    /**
     * Prints the outline header (with the step results of the first example) if it has not been printed yet.
     */
    private function printOutlineHeaderOnce(Formatter $formatter, AfterScenarioTested $event): void
    {
        if ($this->headerPrinted) {
            return;
        }

        $this->tablePrinter->printHeader($formatter, $event->getFeature(), $this->outline, $this->getStepTestResults());
        $this->headerPrinted = true;
    }

    /**
     * Prints the header of the examples table $example belongs to, unless it has already been printed.
     *
     * @return ExampleTableNode the examples table that $example was created from
     */
    private function printExamplesTableHeaderOnTableChange(Formatter $formatter, ExampleNode $example): ExampleTableNode
    {
        $exampleTable = $this->exampleTableResolver->resolveTable($this->outline, $example);

        if ($exampleTable !== $this->printedExampleTable) {
            $this->tablePrinter->printExamplesTableHeader($formatter, $exampleTable);
            $this->printedExampleTable = $exampleTable;
        }

        return $exampleTable;
    }

    /**
     * Prints outline footer on outline AFTER event.
     */
    private function printFooterOnAfterEvent(Formatter $formatter, Event $event): void
    {
        if (!$event instanceof AfterOutlineTested) {
            return;
        }

        $this->tablePrinter->printFooter($formatter, $event->getTestResult());
    }

    /**
     * Returns currently captured step events results.
     *
     * @return array<int,StepResult>
     */
    private function getStepTestResults(): array
    {
        return array_map(
            fn (AfterStepTested $event): StepResult => $event->getTestResult(),
            $this->stepAfterTestedEvents
        );
    }
}
