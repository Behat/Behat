<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Progress;

use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Config\Formatter\ShowOutputOption;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat progress step printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ProgressStepPrinter implements StepPrinter
{
    /**
     * @var ResultToStringConverter
     */
    private $resultConverter;
    /**
     * @var integer
     */
    private $stepsPrinted = 0;

    private bool $hasPrintedOutput = false;

    /**
     * Initializes printer.
     *
     * @param ResultToStringConverter $resultConverter
     */
    public function __construct(ResultToStringConverter $resultConverter)
    {
        $this->resultConverter = $resultConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepResult $result)
    {
        $printer = $formatter->getOutputPrinter();
        $style = $this->resultConverter->convertResultToString($result);

        // After printing any output, we need to print a new line before continuing
        // to print the "dots" of the progress
        if ($this->hasPrintedOutput) {
            $printer->writeln('');
            $this->hasPrintedOutput = false;
        }

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

        $showOutput = $formatter->getParameter(ShowOutputOption::OPTION_NAME);
        if ($showOutput === ShowOutputOption::Yes ||
            ($showOutput === ShowOutputOption::OnFail && !$result->isPassed())) {
            $this->printStdOut($formatter->getOutputPrinter(), $result);
        }

        if (++$this->stepsPrinted % 70 == 0) {
            $printer->writeln(' ' . $this->stepsPrinted);
        }
    }

    /**
     * Prints step output (if has one).
     */
    private function printStdOut(OutputPrinter $printer, StepResult $result): void
    {
        if (!$result instanceof ExecutedStepResult || null === $result->getCallResult()->getStdOut()) {
            return;
        }

        $printer->writeln("\n" . $result->getStepDefinition()->getPath() . ':');
        $callResult = $result->getCallResult();
        $pad = function ($line) {
            return sprintf(
                '  | {+stdout}%s{-stdout}', $line
            );
        };

        $printer->write(implode("\n", array_map($pad, explode("\n", $callResult->getStdOut()))));
        $this->hasPrintedOutput = true;
    }
}
