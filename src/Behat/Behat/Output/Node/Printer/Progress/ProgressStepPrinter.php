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
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;
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
            case StepResult::UNDEFINED:
                $printer->write("{+$style}U{-$style}");
                break;
            case TestResult::FAILED:
                $printer->write("{+$style}F{-$style}");
                break;
        }

        if (++$this->stepsPrinted % 70 == 0) {
            $printer->writeln(' ' . $this->stepsPrinted);
        }
    }
}
