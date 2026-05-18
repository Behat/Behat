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
use Behat\Behat\Output\Node\Printer\Helper\StepTextPainter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Tester\Result\DefinedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResult;

use function strlen;

/**
 * Prints steps as skipped.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PrettySkippedStepPrinter implements StepPrinter
{
    /**
     * @var string
     */
    private $indentText;
    private readonly string $subIndentText;

    /**
     * Initializes printer.
     *
     * @param int $indentation
     * @param int $subIndentation
     */
    public function __construct(
        private readonly StepTextPainter $textPainter,
        private readonly ResultToStringConverter $resultConverter,
        private readonly PrettyPathPrinter $pathPrinter,
        $indentation = 4,
        $subIndentation = 2,
    ) {
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepResult $result): void
    {
        $this->printText($formatter->getOutputPrinter(), $step, $result);
        $this->pathPrinter->printStepPath($formatter, $scenario, $step, $result, mb_strlen($this->indentText, 'utf8'));
        $this->printArguments($formatter, $step->getArguments());
    }

    /**
     * Prints step text.
     */
    private function printText(OutputPrinter $printer, StepNode $step, StepResult $result): void
    {
        $style = $this->resultConverter->convertResultCodeToString(TestResult::SKIPPED);

        if ($result instanceof DefinedStepResult && $result->getStepDefinition()) {
            $definition = $result->getStepDefinition();
            // We render the full text, but should only paint the step text - the keyword should not be modified.
            // It's theoretically possible (e.g. in languages with logograms) that the keyword contains the step text
            // so we can't just str_replace.
            // Instead, use a substring to extract the prefix by excluding the last {length of step text} chars.
            // We can then prepend that to the painted step text to get the final value.
            $stepText = $this->textPainter->paintText(
                $step->getText(),
                $definition,
                new IntegerTestResult(TestResult::SKIPPED)
            );
            $prefix = substr($step->getFullText(), 0, -strlen($step->getText()));
            $fullStepText = $prefix . $stepText;
        } else {
            $fullStepText = $step->getFullText();
        }

        $printer->write(sprintf('%s{+%s}%s{-%s}', $this->indentText, $style, $fullStepText, $style));
    }

    /**
     * Prints step multiline arguments.
     *
     * @param ArgumentInterface[] $arguments
     */
    private function printArguments(Formatter $formatter, array $arguments): void
    {
        $style = $this->resultConverter->convertResultCodeToString(TestResult::SKIPPED);

        foreach ($arguments as $argument) {
            $text = $this->getArgumentString($argument, !$formatter->getParameter('multiline'));

            $indentedText = implode("\n", array_map($this->subIndent(...), explode("\n", $text)));
            $formatter->getOutputPrinter()->writeln(sprintf('{+%s}%s{-%s}', $style, $indentedText, $style));
        }
    }

    /**
     * Returns argument string for provided argument.
     */
    private function getArgumentString(ArgumentInterface $argument, bool $collapse = false): string
    {
        if ($collapse) {
            return '...';
        }

        if ($argument instanceof PyStringNode) {
            $text = '"""' . "\n" . $argument . "\n" . '"""';

            return $text;
        }
        if ($argument instanceof TableNode) {
            return (string) $argument;
        }

        return '';
    }

    /**
     * Indents text to the subIndentation level.
     */
    private function subIndent(string $text): string
    {
        return $this->subIndentText . $text;
    }
}
