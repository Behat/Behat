<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Definition\Translator\TranslatorInterface;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\Output\Node\Printer\ExampleRowPrinter;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Behat\Testwork\Tester\Result\TestResults;

/**
 * Prints example results in form of a table row.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PrettyExampleRowPrinter implements ExampleRowPrinter
{
    /**
     * @var string
     */
    private $indentText;
    /**
     * @var string
     */
    private $subIndentText;

    /**
     * Initializes printer.
     *
     * @param int $indentation
     * @param int $subIndentation
     */
    public function __construct(
        private readonly ResultToStringConverter $resultConverter,
        private readonly ExceptionPresenter $exceptionPresenter,
        private readonly TranslatorInterface $translator,
        $indentation = 6,
        $subIndentation = 2,
    ) {
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    public function printExampleRow(Formatter $formatter, OutlineNode $outline, ExampleNode $example, array $events): void
    {
        $rowNum = array_search($example, $outline->getExamples()) + 1;
        $wrapper = $this->getWrapperClosure($outline, $example, $events);
        $row = $outline->getExampleTable()->getRowAsStringWithWrappedValues($rowNum, $wrapper);

        $formatter->getOutputPrinter()->writeln(sprintf('%s%s', $this->indentText, $row));
        $this->printStepExceptionsAndStdOut($formatter->getOutputPrinter(), $events);
    }

    /**
     * Creates wrapper-closure for the example table.
     *
     * @param AfterStepTested[] $stepEvents
     *
     * @return callable
     */
    private function getWrapperClosure(OutlineNode $outline, ExampleNode $example, array $stepEvents)
    {
        $resultConverter = $this->resultConverter;

        return function ($value, $column) use ($outline, $example, $stepEvents, $resultConverter) {
            $results = [];
            foreach ($stepEvents as $event) {
                $index = array_search($event->getStep(), $example->getSteps());
                $header = $outline->getExampleTable()->getRow(0);
                $steps = $outline->getSteps();
                $outlineStepText = $steps[$index]->getText();

                if (str_contains($outlineStepText, '<' . $header[$column] . '>')) {
                    $results[] = $event->getTestResult();
                }
            }

            $result = new TestResults($results);
            $style = $resultConverter->convertResultToString($result);

            return sprintf('{+%s}%s{-%s}', $style, $value, $style);
        };
    }

    /**
     * Prints step events exceptions (if has some).
     *
     * @param AfterTested[] $events
     */
    private function printStepExceptionsAndStdOut(OutputPrinter $printer, array $events): void
    {
        foreach ($events as $event) {
            $stepResult = $event->getTestResult();
            assert($stepResult instanceof StepResult);
            $this->printStepStdOut($printer, $stepResult);
            $this->printStepException($printer, $event);
        }
    }

    /**
     * Prints step exception (if has one).
     */
    private function printStepException(OutputPrinter $printer, AfterTested $event): void
    {
        $result = $event->getTestResult();

        $style = $this->resultConverter->convertResultToString($result);

        if (!$result instanceof ExceptionResult || !$result->hasException()) {
            return;
        }

        if ($event instanceof AfterStepTested) {
            $title = $this->translator->trans('failed_step_title', [], 'output');
            $step = $event->getStep();
            $printer->writeln(sprintf('{+%s}%s%s %s %s{-%s}', $style, $this->subIndentText, $title, $step->getKeyword(), $step->getText(), $style));
        }

        $text = $this->exceptionPresenter->presentException($result->getException());
        $indentedText = implode("\n", array_map([$this, 'subIndent'], explode("\n", $text)));
        $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $indentedText, $style));
    }

    /**
     * Prints step output (if has one).
     */
    private function printStepStdOut(OutputPrinter $printer, StepResult $result): void
    {
        if (!$result instanceof ExecutedStepResult || null === $result->getCallResult()->getStdOut()) {
            return;
        }

        $callResult = $result->getCallResult();
        $indentedText = $this->subIndentText;

        $pad = (fn ($line): string => sprintf(
            '%s│ {+stdout}%s{-stdout}',
            $indentedText,
            $line
        ));

        $printer->writeln(implode("\n", array_map($pad, explode("\n", (string) $callResult->getStdOut()))));
    }

    /**
     * Indents text to the subIndentation level.
     *
     * @param string $text
     */
    private function subIndent($text): string
    {
        return $this->subIndentText . $text;
    }
}
