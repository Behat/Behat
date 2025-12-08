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
    private readonly ConfigurablePathPrinter $configurablePathPrinter;

    /**
     * Initializes printer.
     */
    public function __construct(
        private readonly WidthCalculator $widthCalculator,
        string $basePath,
        ?ConfigurablePathPrinter $configurablePathPrinter = null,
    ) {
        $this->configurablePathPrinter = $configurablePathPrinter ?? new ConfigurablePathPrinter($basePath, printAbsolutePaths: false, editorUrl: null);
    }

    /**
     * Prints scenario path comment.
     *
     * @param int $indentation
     */
    public function printScenarioPath(Formatter $formatter, FeatureNode $feature, Scenario $scenario, $indentation): void
    {
        $printer = $formatter->getOutputPrinter();

        if (!$formatter->getParameter('paths')) {
            $printer->writeln();

            return;
        }

        $fileAndLine = $this->configurablePathPrinter->processPathsInText(sprintf('%s:%s', $feature->getFile(), $scenario->getLine()));
        $headerWidth = $this->widthCalculator->calculateScenarioHeaderWidth($scenario, $indentation);
        $scenarioWidth = $this->widthCalculator->calculateScenarioWidth($scenario, $indentation, 2);
        $spacing = str_repeat(' ', max(0, $scenarioWidth - $headerWidth));

        $printer->writeln(sprintf('%s {+comment}# %s{-comment}', $spacing, $fileAndLine));
    }

    /**
     * Prints step path comment.
     *
     * @param int $indentation
     */
    public function printStepPath(
        Formatter $formatter,
        Scenario $scenario,
        StepNode $step,
        StepResult $result,
        $indentation,
    ): void {
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
     * @param int $scenarioWidth
     * @param int $stepWidth
     */
    private function printDefinedStepPath(OutputPrinter $printer, DefinedStepResult $result, $scenarioWidth, $stepWidth): void
    {
        $path = $result->getStepDefinition()->getPath();
        $spacing = str_repeat(' ', max(0, $scenarioWidth - $stepWidth));

        $printer->writeln(sprintf('%s {+comment}# %s{-comment}', $spacing, $path));
    }
}
