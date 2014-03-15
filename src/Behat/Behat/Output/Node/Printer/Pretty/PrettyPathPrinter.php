<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\Helper\WidthCalculator;
use Behat\Behat\Tester\Result\DefinedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;

/**
 * Prints paths for scenarios, examples, backgrounds and steps.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PrettyPathPrinter
{
    /**
     * @var WidthCalculator
     */
    private $widthCalculator;

    /**
     * Initializes printer.
     *
     * @param WidthCalculator $widthCalculator
     */
    public function __construct(WidthCalculator $widthCalculator)
    {
        $this->widthCalculator = $widthCalculator;
    }

    /**
     * Prints step path.
     *
     * @param Formatter  $formatter
     * @param Scenario   $scenario
     * @param StepNode   $step
     * @param StepResult $result
     * @param integer    $indentation
     */
    public function printStepPath(
        Formatter $formatter,
        Scenario $scenario,
        StepNode $step,
        StepResult $result,
        $indentation
    ) {
        $printer = $formatter->getOutputPrinter();

        if (!$formatter->getParameter('paths') || !$this->resultHasDefinition($result)) {
            $printer->writeln();

            return;
        }

        $textWidth = $this->widthCalculator->calculateStepWidth($step, $indentation);
        $scenarioWidth = $this->widthCalculator->calculateScenarioWidth($scenario, $indentation - 2, 2);

        $this->printDefinedStepPath($printer, $result, $scenarioWidth, $textWidth);
    }

    /**
     * Checks if result has step definition.
     *
     * @param StepResult $result
     *
     * @return Boolean
     */
    private function resultHasDefinition(StepResult $result)
    {
        return $result instanceof DefinedStepResult && $result->getStepDefinition();
    }

    /**
     * Prints defined step path.
     *
     * @param OutputPrinter     $printer
     * @param DefinedStepResult $result
     * @param integer           $scenarioWidth
     * @param integer           $stepWidth
     */
    private function printDefinedStepPath(OutputPrinter $printer, DefinedStepResult $result, $scenarioWidth, $stepWidth)
    {
        $path = $result->getStepDefinition()->getPath();
        $spacing = str_repeat(' ', $scenarioWidth - $stepWidth);

        $printer->writeln(sprintf('%s {+comment}# %s{-comment}', $spacing, $path));
    }
}
