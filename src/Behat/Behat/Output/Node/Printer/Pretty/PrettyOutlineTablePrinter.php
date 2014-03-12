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
use Behat\Behat\Output\Node\Printer\OutlineTablePrinter;
use Behat\Behat\Output\Node\Printer\ScenarioPrinter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints outline table header and footer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PrettyOutlineTablePrinter implements OutlineTablePrinter
{
    /**
     * @var ScenarioPrinter
     */
    private $scenarioPrinter;
    /**
     * @var StepPrinter
     */
    private $stepPrinter;
    /**
     * @var ResultToStringConverter
     */
    private $resultConverter;
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
     * @param ScenarioPrinter         $scenarioPrinter
     * @param StepPrinter             $stepPrinter
     * @param ResultToStringConverter $resultConverter
     * @param integer                 $indentation
     * @param integer                 $subIndentation
     */
    public function __construct(
        ScenarioPrinter $scenarioPrinter,
        StepPrinter $stepPrinter,
        ResultToStringConverter $resultConverter,
        $indentation = 4,
        $subIndentation = 2
    ) {
        $this->scenarioPrinter = $scenarioPrinter;
        $this->stepPrinter = $stepPrinter;
        $this->resultConverter = $resultConverter;
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    /**
     * {@inheritdoc}
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature, OutlineNode $outline, array $results)
    {
        $this->scenarioPrinter->printHeader($formatter, $feature, $outline);

        $this->printExamplesSteps($formatter, $outline, $outline->getSteps(), $results);
        $this->printExamplesTableHeader($formatter->getOutputPrinter(), $outline->getExampleTable());
    }

    /**
     * {@inheritdoc}
     */
    public function printFooter(Formatter $formatter, TestResult $result)
    {
        $formatter->getOutputPrinter()->writeln();
    }

    /**
     * Prints example steps with definition paths (if has some), but without exceptions or state (skipped).
     *
     * @param Formatter    $formatter
     * @param OutlineNode  $outline
     * @param StepNode[]   $steps
     * @param StepResult[] $results
     */
    private function printExamplesSteps(Formatter $formatter, OutlineNode $outline, array $steps, array $results)
    {
        foreach ($steps as $step) {
            $result = $results[$step->getLine()];

            $this->stepPrinter->printStep($formatter, $outline, $step, $result);
        }

        $formatter->getOutputPrinter()->writeln();
    }

    /**
     * Prints examples table header.
     *
     * @param OutputPrinter    $printer
     * @param ExampleTableNode $table
     */
    private function printExamplesTableHeader(OutputPrinter $printer, ExampleTableNode $table)
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

        return function ($col) use ($style) {
            return sprintf('{+%s_param}%s{-%s_param}', $style, $col, $style);
        };
    }
}
