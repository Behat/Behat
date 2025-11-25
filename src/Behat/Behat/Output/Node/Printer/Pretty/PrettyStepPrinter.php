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
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Formatter\ShowOutputOption;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints step.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PrettyStepPrinter implements StepPrinter
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
        private readonly ExceptionPresenter $exceptionPresenter,
        $indentation = 4,
        $subIndentation = 2,
    ) {
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepResult $result)
    {
        if ($result->getResultCode() === TestResult::SKIPPED) {
            $printSkipped = $formatter->getParameter(PrettyFormatter::PRINT_SKIPPED_STEPS_SETTING);

            if ($printSkipped === false) {
                return;
            }
        }

        $this->printText($formatter->getOutputPrinter(), $step->getKeyword(), $step->getText(), $result);
        $this->pathPrinter->printStepPath($formatter, $scenario, $step, $result, mb_strlen($this->indentText, 'utf8'));
        $this->printArguments($formatter, $step->getArguments(), $result);
        $showOutput = $formatter->getParameter(ShowOutputOption::OPTION_NAME);
        if ($showOutput === null || $showOutput === ShowOutputOption::Yes
            || ($showOutput === ShowOutputOption::OnFail && !$result->isPassed())) {
            $this->printStdOut($formatter->getOutputPrinter(), $result);
        }
        $this->printException($formatter->getOutputPrinter(), $result);
    }

    /**
     * Prints step text.
     *
     * @param string        $stepType
     * @param string        $stepText
     */
    private function printText(OutputPrinter $printer, $stepType, $stepText, StepResult $result)
    {
        if ($result instanceof DefinedStepResult && $result->getStepDefinition()) {
            $definition = $result->getStepDefinition();
            $stepText = $this->textPainter->paintText($stepText, $definition, $result);
        }

        $style = $this->resultConverter->convertResultToString($result);
        $printer->write(sprintf('%s{+%s}%s %s{-%s}', $this->indentText, $style, $stepType, $stepText, $style));
    }

    /**
     * Prints step multiline arguments.
     *
     * @param ArgumentInterface[] $arguments
     */
    private function printArguments(Formatter $formatter, array $arguments, StepResult $result)
    {
        $style = $this->resultConverter->convertResultToString($result);

        foreach ($arguments as $argument) {
            $text = $this->getArgumentString($argument, !$formatter->getParameter('multiline'));

            $indentedText = implode("\n", array_map([$this, 'subIndent'], explode("\n", $text)));
            $formatter->getOutputPrinter()->writeln(sprintf('{+%s}%s{-%s}', $style, $indentedText, $style));
        }
    }

    /**
     * Prints step output (if has one).
     */
    private function printStdOut(OutputPrinter $printer, StepResult $result)
    {
        if (!$result instanceof ExecutedStepResult || null === $result->getCallResult()->getStdOut()) {
            return;
        }

        $callResult = $result->getCallResult();
        $indentedText = $this->subIndentText;

        $pad = (fn ($line) => sprintf(
            '%s│ {+stdout}%s{-stdout}',
            $indentedText,
            $line
        ));

        $printer->writeln(implode("\n", array_map($pad, explode("\n", (string) $callResult->getStdOut()))));
    }

    /**
     * Prints step exception (if has one).
     */
    private function printException(OutputPrinter $printer, StepResult $result)
    {
        $style = $this->resultConverter->convertResultToString($result);

        if (!$result instanceof ExceptionResult || !$result->hasException()) {
            return;
        }

        $text = $this->exceptionPresenter->presentException($result->getException());
        $indentedText = implode("\n", array_map([$this, 'subIndent'], explode("\n", $text)));
        $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $indentedText, $style));
    }

    /**
     * Returns argument string for provided argument.
     *
     * @param bool           $collapse
     *
     * @return string
     */
    private function getArgumentString(ArgumentInterface $argument, $collapse = false)
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
     *
     * @return string
     */
    private function subIndent($text)
    {
        return $this->subIndentText . $text;
    }
}
