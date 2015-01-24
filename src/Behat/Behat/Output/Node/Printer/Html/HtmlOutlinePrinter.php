<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Html;

use Behat\Behat\Output\Node\Printer\Helper\HtmlPrinter;
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
final class HtmlOutlinePrinter implements OutlinePrinter
{
    /**
     * @var HtmlPrinter
     */
    private $htmlPrinter;

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
     * Initializes printer.
     *
     * @param HtmlPrinter $htmlPrinter
     * @param ScenarioPrinter $scenarioPrinter
     * @param StepPrinter $stepPrinter
     * @param ResultToStringConverter $resultConverter
     */
    public function __construct(
        HtmlPrinter $htmlPrinter,
        ScenarioPrinter $scenarioPrinter,
        StepPrinter $stepPrinter,
        ResultToStringConverter $resultConverter
    ) {
        $this->scenarioPrinter = $scenarioPrinter;
        $this->stepPrinter = $stepPrinter;
        $this->resultConverter = $resultConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature, OutlineNode $outline)
    {
        $this->scenarioPrinter->printHeader($formatter, $feature, $outline);

        $this->printExamplesSteps($formatter, $outline, $outline->getSteps());
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
     * Prints outline steps.
     *
     * @param Formatter $formatter
     * @param OutlineNode $outline
     * @param StepNode[] $steps
     */
    private function printExamplesSteps(Formatter $formatter, OutlineNode $outline, array $steps)
    {
        foreach ($steps as $step) {
            $this->stepPrinter->printStep($formatter, $outline, $step, new UndefinedStepResult());
        }

        $formatter->getOutputPrinter()->writeln();
    }

    /**
     * Prints examples table header.
     *
     * @param OutputPrinter $printer
     * @param ExampleTableNode $table
     */
    private function printExamplesTableHeader(OutputPrinter $printer, ExampleTableNode $table)
    {
        $printer->writeln($table->getKeyword());

        $rowNum = 0;
        $wrapper = $this->getWrapperClosure();
        $row = $table->getRowAsStringWithWrappedValues($rowNum, $wrapper);

        $printer->writeln($row);
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

    /**
     * @param OutputPrinter $printer
     * @return HtmlPrinter
     */
    protected function getHtmlPrinter(OutputPrinter $printer)
    {
        $this->htmlPrinter->setOutputPrinter($printer);
        return $this->htmlPrinter;
    }

    /**
     * @param HtmlPrinter $htmlPrinter
     * @return $this
     */
    protected function setHtmlPrinter(HtmlPrinter $htmlPrinter)
    {
        $this->htmlPrinter = $htmlPrinter;
        return $this;
    }
}
