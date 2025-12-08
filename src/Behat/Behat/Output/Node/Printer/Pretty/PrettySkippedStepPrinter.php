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
        $this->printText($formatter->getOutputPrinter(), $step->getKeyword(), $step->getText(), $result);
        $this->pathPrinter->printStepPath($formatter, $scenario, $step, $result, mb_strlen($this->indentText, 'utf8'));
        $this->printArguments($formatter, $step->getArguments());
    }

    /**
     * Prints step text.
     *
     * @param string        $stepType
     * @param string        $stepText
     */
    private function printText(OutputPrinter $printer, $stepType, $stepText, StepResult $result): void
    {
        $style = $this->resultConverter->convertResultCodeToString(TestResult::SKIPPED);

        if ($result instanceof DefinedStepResult && $result->getStepDefinition()) {
            $definition = $result->getStepDefinition();
            $stepText = $this->textPainter->paintText(
                $stepText,
                $definition,
                new IntegerTestResult(TestResult::SKIPPED)
            );
        }

        $printer->write(sprintf('%s{+%s}%s %s{-%s}', $this->indentText, $style, $stepType, $stepText, $style));
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

            $indentedText = implode("\n", array_map([$this, 'subIndent'], explode("\n", $text)));
            $formatter->getOutputPrinter()->writeln(sprintf('{+%s}%s{-%s}', $style, $indentedText, $style));
        }
    }

    /**
     * Returns argument string for provided argument.
     *
     * @param bool           $collapse
     */
    private function getArgumentString(ArgumentInterface $argument, $collapse = false): string
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
     *
     * @param string $text
     */
    private function subIndent($text): string
    {
        return $this->subIndentText . $text;
    }
}
