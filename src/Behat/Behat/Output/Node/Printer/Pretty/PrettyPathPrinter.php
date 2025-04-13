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
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\PathOptions\Printer\ConfigurablePathPrinter;

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

    private ConfigurablePathPrinter $configurablePathPrinter;

    /**
     * Initializes printer.
     *
     * @param WidthCalculator $widthCalculator
     * @param string          $basePath deprecated, will be removed in next major version
     */
    public function __construct(WidthCalculator $widthCalculator, string $basePath)
    {
        $this->widthCalculator = $widthCalculator;
    }

    public function setConfigurablePathPrinter(ConfigurablePathPrinter $configurablePathPrinter)
    {
        $this->configurablePathPrinter = $configurablePathPrinter;
    }

    /**
     * Prints scenario path comment.
     *
     * @param Formatter   $formatter
     * @param FeatureNode $feature
     * @param Scenario    $scenario
     * @param integer     $indentation
     */
    public function printScenarioPath(Formatter $formatter, FeatureNode $feature, Scenario $scenario, $indentation)
    {
        $printer = $formatter->getOutputPrinter();

        if (!$formatter->getParameter('paths')) {
            $printer->writeln();

            return;
        }

        $fileAndLine = sprintf('%s:%s', $this->configurablePathPrinter->processPathsInText($feature->getFile()), $scenario->getLine());
        $headerWidth = $this->widthCalculator->calculateScenarioHeaderWidth($scenario, $indentation);
        $scenarioWidth = $this->widthCalculator->calculateScenarioWidth($scenario, $indentation, 2);
        $spacing = str_repeat(' ', max(0, $scenarioWidth - $headerWidth));

        $printer->writeln(sprintf('%s {+comment}# %s{-comment}', $spacing, $fileAndLine));
    }

    /**
     * Prints step path comment.
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
        $indentation,
    ) {
        $printer = $formatter->getOutputPrinter();

        if (!$result instanceof DefinedStepResult || !$result->getStepDefinition() || !$formatter->getParameter('paths')) {
            $printer->writeln();

            return;
        }

        $textWidth = $this->widthCalculator->calculateStepWidth($step, $indentation);
        $scenarioWidth = $this->widthCalculator->calculateScenarioWidth($scenario, $indentation - 2, 2);

        $this->printDefinedStepPath($printer, $result, $scenarioWidth, $textWidth);
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
        $spacing = str_repeat(' ', max(0, $scenarioWidth - $stepWidth));

        $printer->writeln(sprintf('%s {+comment}# %s{-comment}', $spacing, $path));
    }
}
