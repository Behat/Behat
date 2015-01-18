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
 * @author Ali Bahman <abn@webit4.me>
 */
final class HtmlOutlineTablePrinter extends AbstractHtmlPrinter implements OutlineTablePrinter
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
        $this->setHtmlPrinter($htmlPrinter);
        $this->scenarioPrinter = $scenarioPrinter;
        $this->stepPrinter = $stepPrinter;
        $this->resultConverter = $resultConverter;
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
        $this->scenarioPrinter->printFooter($formatter, $result);
    }

    /**
     * Prints example steps with definition paths (if has some), but without exceptions or state (skipped).
     *
     * @param Formatter $formatter
     * @param OutlineNode $outline
     * @param StepNode[] $steps
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
     * @param OutputPrinter $printer
     * @param ExampleTableNode $table
     */
    private function printExamplesTableHeader(OutputPrinter $printer, ExampleTableNode $table)
    {

        $printer->writeln(
            array(
                '<table class="table table-bordered">',
                '    <thead>',
                '       <tr>'
            )
        );

        $columnTag = 'th';
        foreach ($table->getRows() as $row) {
            $printer->writeln('<tr>');
            foreach ($row as $columnTitle) {
                $printer->write(
                    array(
                        "<$columnTag>",
                        $columnTitle,
                        "</$columnTag>"
                    )
                );
            }
            $printer->writeln('</tr>');
            $columnTag = 'td';
        }

        $printer->writeln('</table>');
    }
}
