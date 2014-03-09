<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\ExamplePrinter;
use Behat\Behat\Output\Node\Printer\Helper\WidthCalculator;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat pretty example printer.
 *
 * Prints example header (usually simply an example row) and footer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyExamplePrinter implements ExamplePrinter
{
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
    private $basePath;

    /**
     * Initializes printer.
     *
     * @param \Behat\Behat\Output\Node\Printer\Helper\WidthCalculator $widthCalculator
     * @param string          $basePath
     * @param integer         $indentation
     */
    public function __construct(WidthCalculator $widthCalculator, $basePath, $indentation = 6)
    {
        $this->widthCalculator = $widthCalculator;
        $this->basePath = $basePath;
        $this->indentText = str_repeat(' ', intval($indentation));
    }

    /**
     * {@inheritdoc}
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature, ExampleNode $example)
    {
        $this->printTitle($formatter->getOutputPrinter(), $example);

        if ($formatter->getParameter('paths')) {
            $this->printPath($formatter->getOutputPrinter(), $feature, $example);
        } else {
            $formatter->getOutputPrinter()->writeln();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function printFooter(Formatter $formatter, FeatureNode $feature, ExampleNode $example, TestResult $result)
    {
    }

    /**
     * Prints example title.
     *
     * @param OutputPrinter $printer
     * @param ExampleNode   $example
     */
    private function printTitle(OutputPrinter $printer, ExampleNode $example)
    {
        $printer->write(sprintf('%s%s', $this->indentText, $example->getTitle()));
    }

    /**
     * Prints scenario path comment.
     *
     * @param OutputPrinter $printer
     * @param FeatureNode   $feature
     * @param ExampleNode   $example
     */
    private function printPath(OutputPrinter $printer, FeatureNode $feature, ExampleNode $example)
    {
        $indentation = mb_strlen($this->indentText, 'utf8');

        $fileAndLine = sprintf('%s:%s', $this->relativizePaths($feature->getFile()), $example->getLine());
        $headerWidth = $this->widthCalculator->calculateScenarioHeaderWidth($example, $indentation);
        $scenarioWidth = $this->widthCalculator->calculateScenarioWidth($example, $indentation);
        $spacing = str_repeat(' ', max(0, $scenarioWidth - $headerWidth));

        $printer->writeln(sprintf('%s {+comment}# %s{-comment}', $spacing, $fileAndLine));
    }

    /**
     * Transforms path to relative.
     *
     * @param string $path
     *
     * @return string
     */
    private function relativizePaths($path)
    {
        if (!$this->basePath) {
            return $path;
        }

        return str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $path);
    }
}
