<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\OutlinePrinter;
use Behat\Behat\Output\Node\Printer\ScenarioPrinter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Tester\Result\UndefinedStepResult;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints outline header with outline steps and table header.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PrettyOutlinePrinter implements OutlinePrinter
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
     * @param int $indentation
     * @param int $subIndentation
     */
    public function __construct(
        private readonly ScenarioPrinter $scenarioPrinter,
        private readonly StepPrinter $stepPrinter,
        private readonly ResultToStringConverter $resultConverter,
        $indentation = 4,
        $subIndentation = 2,
    ) {
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    public function printHeader(Formatter $formatter, FeatureNode $feature, OutlineNode $outline): void
    {
        $this->scenarioPrinter->printHeader($formatter, $feature, $outline);

        $this->printExamplesSteps($formatter, $outline, $outline->getSteps());
        $this->printExamplesTableHeader($formatter->getOutputPrinter(), $outline->getExampleTable());
    }

    public function printFooter(Formatter $formatter, TestResult $result): void
    {
        $formatter->getOutputPrinter()->writeln();
    }

    /**
     * Prints outline steps.
     *
     * @param StepNode[]  $steps
     */
    private function printExamplesSteps(Formatter $formatter, OutlineNode $outline, array $steps): void
    {
        foreach ($steps as $step) {
            $this->stepPrinter->printStep($formatter, $outline, $step, new UndefinedStepResult());
        }

        $formatter->getOutputPrinter()->writeln();
    }

    /**
     * Prints examples table header.
     */
    private function printExamplesTableHeader(OutputPrinter $printer, ExampleTableNode $table): void
    {
        $printer->writeln(sprintf('%s{+keyword}%s:{-keyword}', $this->indentText, $table->getKeyword()));

        $rowNum = 0;
        $wrapper = $this->getWrapperClosure();
        $row = $table->getRowAsStringWithWrappedValues($rowNum, $wrapper);

        $printer->writeln(sprintf('%s%s', $this->subIndentText, $row));
    }

    /**
     * Creates wrapper-closure for the example header.
     *
     * @return callable
     */
    private function getWrapperClosure()
    {
        $style = $this->resultConverter->convertResultCodeToString(TestResult::SKIPPED);

        return fn ($col): string => sprintf('{+%s_param}%s{-%s_param}', $style, $col, $style);
    }
}
