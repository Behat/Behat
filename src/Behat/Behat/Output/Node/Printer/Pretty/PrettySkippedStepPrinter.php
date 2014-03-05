<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\SkippedStepPrinter;
use Behat\Behat\Tester\Result\StepTestResult;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;
use Prophecy\Doubler\Generator\Node\ArgumentNode;

/**
 * Behat pretty skipped step printer.
 *
 * Prints steps as skipped.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettySkippedStepPrinter implements SkippedStepPrinter
{
    /**
     * @var StepTextPainter
     */
    private $textPainter;
    /**
     * @var WidthCalculator
     */
    private $widthCalculator;
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
     * @param StepTextPainter $textPainter
     * @param WidthCalculator $widthCalculator
     * @param integer         $indentation
     * @param integer         $subIndentation
     */
    public function __construct(
        StepTextPainter $textPainter,
        WidthCalculator $widthCalculator,
        $indentation = 4,
        $subIndentation = 2
    ) {
        $this->textPainter = $textPainter;
        $this->widthCalculator = $widthCalculator;
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    /**
     * {@inheritdoc}
     */
    public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepTestResult $result = null)
    {
        $result = $result ? : new StepTestResult();

        $this->printText($formatter->getOutputPrinter(), $step->getType(), $step->getText(), $result);

        if ($formatter->getParameter('paths')) {
            $this->printPath($formatter->getOutputPrinter(), $scenario, $step, $result);
        } else {
            $formatter->getOutputPrinter()->writeln();
        }

        $this->printArguments($formatter, $step->getArguments());
    }

    /**
     * Prints step text.
     *
     * @param OutputPrinter  $printer
     * @param string         $stepType
     * @param string         $stepText
     * @param StepTestResult $result
     */
    private function printText(OutputPrinter $printer, $stepType, $stepText, StepTestResult $result)
    {
        $style = new TestResult(StepTestResult::SKIPPED);

        if ($result->hasFoundDefinition()) {
            $definition = $result->getSearchResult()->getMatchedDefinition();
            $stepText = $this->textPainter->paintText($stepText, $definition, $style);
        }

        $printer->write(sprintf('%s{+%s}%s %s{-%s}', $this->indentText, $style, $stepType, $stepText, $style));
    }

    /**
     * Prints step definition path (if has one).
     *
     * @param OutputPrinter  $printer
     * @param Scenario       $scenario
     * @param StepNode       $step
     * @param StepTestResult $result
     */
    private function printPath(OutputPrinter $printer, Scenario $scenario, StepNode $step, StepTestResult $result)
    {
        if (!$result->hasFoundDefinition()) {
            $printer->writeln();

            return;
        }

        $path = $result->getSearchResult()->getMatchedDefinition()->getPath();
        $textWidth = $this->widthCalculator->calculateStepWidth($step);
        $scenarioWidth = $this->widthCalculator->calculateScenarioWidth($scenario);
        $spacing = str_repeat(' ', $scenarioWidth - $textWidth);

        $printer->writeln(sprintf('%s {+comment}# %s{-comment}', $spacing, $path));
    }

    /**
     * Prints step multiline arguments.
     *
     * @param Formatter           $formatter
     * @param ArgumentInterface[] $arguments
     */
    private function printArguments(Formatter $formatter, array $arguments)
    {
        $result = new TestResult(TestResult::SKIPPED);

        foreach ($arguments as $argument) {
            $text = $this->getArgumentString($argument, !$formatter->getParameter('multiline'));

            $indentedText = implode("\n", array_map(array($this, 'subIndent'), explode("\n", $text)));
            $formatter->getOutputPrinter()->writeln(sprintf('{+%s}%s{-%s}', $result, $indentedText, $result));
        }
    }

    /**
     * Returns argument string for provided argument.
     *
     * @param ArgumentInterface $argument
     * @param Boolean           $collapse
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

        return (string)$argument;
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
