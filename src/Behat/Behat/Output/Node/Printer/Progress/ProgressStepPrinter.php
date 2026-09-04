<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Progress;

use Behat\Behat\Output\Node\EventListener\AST\CurrentFeatureListener;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Config\Formatter\ProgressFormatter;
use Behat\Config\Formatter\ShowOutputOption;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\PathOptions\Printer\ConfigurablePathPrinter;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat progress step printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ProgressStepPrinter implements StepPrinter
{
    /**
     * Step result codes that carry a problem worth reporting inline.
     */
    private const INLINE_RESULT_CODES = [TestResult::FAILED, TestResult::PENDING, TestResult::UNDEFINED];

    private int $stepsPrinted = 0;

    private bool $hasPrintedOutput = false;

    private bool $midLine = false;

    /**
     * Initializes printer.
     */
    public function __construct(
        private readonly ResultToStringConverter $resultConverter,
        private readonly ?ExceptionPresenter $exceptionPresenter = null,
        private readonly ?ConfigurablePathPrinter $configurablePathPrinter = null,
        private readonly ?CurrentFeatureListener $currentFeatureListener = null,
    ) {
    }

    public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepResult $result): void
    {
        $printer = $formatter->getOutputPrinter();
        $style = $this->resultConverter->convertResultToString($result);

        // After printing any output, we need to print a new line before continuing
        // to print the "dots" of the progress
        if ($this->hasPrintedOutput) {
            $printer->writeln('');
            $this->hasPrintedOutput = false;
            $this->midLine = false;
        }

        if ($this->shouldPrintInline($formatter, $result)) {
            $this->printInlineProblem($printer, $step, $result, $style);
        } else {
            switch ($result->getResultCode()) {
                case TestResult::PASSED:
                    $printer->write("{+$style}.{-$style}");
                    break;
                case TestResult::SKIPPED:
                    $printer->write("{+$style}-{-$style}");
                    break;
                case TestResult::PENDING:
                    $printer->write("{+$style}P{-$style}");
                    break;
                case TestResult::UNDEFINED:
                    $printer->write("{+$style}U{-$style}");
                    break;
                case TestResult::FAILED:
                    $printer->write("{+$style}F{-$style}");
                    break;
            }

            $this->midLine = true;
        }

        $showOutput = $formatter->getParameter(ShowOutputOption::OPTION_NAME);
        if ($showOutput === ShowOutputOption::Yes
            || ($showOutput === ShowOutputOption::OnFail && !$result->isPassed())) {
            $this->printStdOut($formatter->getOutputPrinter(), $result);
        }

        if (++$this->stepsPrinted % 70 === 0) {
            $printer->writeln(' ' . $this->stepsPrinted);
            $this->midLine = false;
        }
    }

    /**
     * Whether the failure/pending/undefined detail of this step should be printed
     * inline rather than waiting for the end-of-run summary.
     */
    private function shouldPrintInline(Formatter $formatter, StepResult $result): bool
    {
        if (!$formatter->getParameter(ProgressFormatter::INLINE_FAILURES_SETTING)) {
            return false;
        }

        if (!$this->configurablePathPrinter instanceof ConfigurablePathPrinter) {
            return false;
        }

        return in_array($result->getResultCode(), self::INLINE_RESULT_CODES, true);
    }

    /**
     * Prints the detail of a failed, pending or undefined step inline, in place
     * of the single-character marker.
     */
    private function printInlineProblem(OutputPrinter $printer, StepNode $step, StepResult $result, string $style): void
    {
        $label = strtoupper($style);

        // Break out of the line of progress markers before printing the block,
        // but only when something has already been written on the current line.
        if ($this->midLine) {
            $printer->writeln('');
            $this->midLine = false;
        }

        $printer->writeln(sprintf('{+%s}--- %s ---{-%s}', $style, $label, $style));
        $printer->writeln(sprintf(
            '    {+%s}%s{-%s} {+comment}# %s{-comment}',
            $style,
            $step->getFullText(),
            $style,
            $this->resolveStepPath($step)
        ));

        $this->printException($printer, $result, $style);

        $printer->writeln(sprintf('{+%s}------------{-%s}', $style, $style));
    }

    /**
     * Prints the presented exception of a step (if it carries one).
     */
    private function printException(OutputPrinter $printer, StepResult $result, string $style): void
    {
        if (!$this->exceptionPresenter instanceof ExceptionPresenter) {
            return;
        }

        if (!$result instanceof ExceptionResult || !$result->hasException()) {
            return;
        }

        $exception = $result->getException();
        if ($exception === null) {
            return;
        }

        $pad = static fn (string $line): string => '      ' . $line;
        $text = $this->exceptionPresenter->presentException($exception);
        $indented = implode("\n", array_map($pad, explode("\n", $text)));

        $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $indented, $style));
    }

    /**
     * Builds the "feature_file:line" reference for a step, honouring the path
     * options (relative/absolute, editor URL) when a printer is available.
     */
    private function resolveStepPath(StepNode $step): string
    {
        $feature = $this->currentFeatureListener?->getCurrentFeature();
        $file = $feature?->getFile();
        $path = $file !== null ? sprintf('%s:%d', $file, $step->getLine()) : (string) $step->getLine();

        if (!$this->configurablePathPrinter instanceof ConfigurablePathPrinter) {
            return $path;
        }

        return $this->configurablePathPrinter->processPathsInText($path);
    }

    /**
     * Prints step output (if has one).
     */
    private function printStdOut(OutputPrinter $printer, StepResult $result): void
    {
        if (!$result instanceof ExecutedStepResult || null === $result->getStdOut()) {
            return;
        }

        $printer->writeln("\n" . $result->getStepDefinition()->getPath() . ':');
        $pad = (fn ($line): string => sprintf(
            '  | {+stdout}%s{-stdout}',
            $line
        ));

        $printer->write(implode("\n", array_map($pad, explode("\n", (string) $result->getStdOut()))));
        $this->hasPrintedOutput = true;
    }
}
