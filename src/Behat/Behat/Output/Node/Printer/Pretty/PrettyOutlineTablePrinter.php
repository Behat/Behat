<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\OutlineTablePrinter;
use Behat\Behat\Output\Node\Printer\ScenarioPrinter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints outline header with outline steps, and the header of each of its examples tables.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PrettyOutlineTablePrinter implements OutlineTablePrinter
{
    private readonly string $indentText;
    private readonly string $subIndentText;

    /**
     * Initializes printer.
     */
    public function __construct(
        private readonly ScenarioPrinter $scenarioPrinter,
        private readonly StepPrinter $stepPrinter,
        private readonly PrettyExamplesTableHeaderPrinter $examplesTableHeaderPrinter,
        int $indentation = 4,
        int $subIndentation = 2,
    ) {
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    public function printHeader(Formatter $formatter, FeatureNode $feature, OutlineNode $outline, array $results): void
    {
        $this->scenarioPrinter->printHeader($formatter, $feature, $outline);

        $this->printExamplesSteps($formatter, $outline, $outline->getSteps(), $results);
    }

    public function printExamplesTableHeader(Formatter $formatter, ExampleTableNode $table): void
    {
        $this->examplesTableHeaderPrinter->printHeader(
            $formatter->getOutputPrinter(),
            $table,
            $this->indentText,
            $this->subIndentText,
        );
    }

    public function printFooter(Formatter $formatter, TestResult $result): void
    {
        $formatter->getOutputPrinter()->writeln();
    }

    /**
     * Prints example steps with definition paths (if has some), but without exceptions or state (skipped).
     *
     * @param StepNode[]   $steps
     * @param StepResult[] $results
     */
    private function printExamplesSteps(Formatter $formatter, OutlineNode $outline, array $steps, array $results): void
    {
        foreach ($steps as $step) {
            $result = $results[$step->getLine()];

            $this->stepPrinter->printStep($formatter, $outline, $step, $result);
        }
    }
}
