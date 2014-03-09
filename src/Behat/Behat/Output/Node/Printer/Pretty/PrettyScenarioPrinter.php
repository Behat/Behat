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
use Behat\Behat\Output\Node\Printer\ScenarioPrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\TaggedNodeInterface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat scenario printer.
 *
 * Prints scenario headers (with tags, keyword and long title) and footers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyScenarioPrinter implements ScenarioPrinter
{
    /**
     * @var WidthCalculator
     */
    private $widthCalculator;
    /**
     * @var string
     */
    private $basePath;
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
     * @param WidthCalculator $widthCalculator
     * @param string          $basePath
     * @param integer         $indentation
     * @param integer         $subIndentation
     */
    public function __construct(
        WidthCalculator $widthCalculator,
        $basePath,
        $indentation = 2,
        $subIndentation = 2
    ) {
        $this->widthCalculator = $widthCalculator;
        $this->basePath = $basePath;
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    /**
     * {@inheritdoc}
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature, Scenario $scenario)
    {
        if ($scenario instanceof TaggedNodeInterface) {
            $this->printTags($formatter->getOutputPrinter(), $scenario->getTags());
        }

        $this->printKeyword($formatter->getOutputPrinter(), $scenario->getKeyword());
        $this->printTitle($formatter->getOutputPrinter(), $scenario->getTitle());

        if ($formatter->getParameter('paths')) {
            $this->printPath($formatter->getOutputPrinter(), $feature, $scenario);
        } else {
            $formatter->getOutputPrinter()->writeln();
        }

        $this->printDescription($formatter->getOutputPrinter(), $scenario->getTitle());
    }

    /**
     * {@inheritdoc}
     */
    public function printFooter(Formatter $formatter, FeatureNode $feature, Scenario $scenario, TestResult $result)
    {
        $formatter->getOutputPrinter()->writeln();
    }

    /**
     * Prints scenario tags.
     *
     * @param OutputPrinter $printer
     * @param string[]      $tags
     */
    private function printTags(OutputPrinter $printer, array $tags)
    {
        if (!count($tags)) {
            return;
        }

        $tags = array_map(array($this, 'prependTagWithTagSign'), $tags);
        $printer->writeln(sprintf('%s{+tag}%s{-tag}', $this->indentText, implode(' ', $tags)));
    }

    /**
     * Prints scenario keyword.
     *
     * @param OutputPrinter $printer
     * @param string        $keyword
     */
    private function printKeyword(OutputPrinter $printer, $keyword)
    {
        $printer->write(sprintf('%s{+keyword}%s:{-keyword}', $this->indentText, $keyword));
    }

    /**
     * Prints scenario title (first line of long title).
     *
     * @param OutputPrinter $printer
     * @param string        $longTitle
     */
    private function printTitle(OutputPrinter $printer, $longTitle)
    {
        $description = explode("\n", $longTitle);
        $title = array_shift($description);

        $printer->write(sprintf(' %s', $title));
    }

    /**
     * Prints scenario path comment.
     *
     * @param OutputPrinter $printer
     * @param FeatureNode   $feature
     * @param Scenario      $scenario
     */
    private function printPath(OutputPrinter $printer, FeatureNode $feature, Scenario $scenario)
    {
        $fileAndLine = sprintf('%s:%s', $this->relativizePaths($feature->getFile()), $scenario->getLine());
        $headerWidth = $this->widthCalculator->calculateScenarioHeaderWidth($scenario);
        $scenarioWidth = $this->widthCalculator->calculateScenarioWidth($scenario);
        $spacing = str_repeat(' ', max(0, $scenarioWidth - $headerWidth));

        $printer->writeln(sprintf('%s {+comment}# %s{-comment}', $spacing, $fileAndLine));
    }

    /**
     * Prints scenario description (other lines of long title).
     *
     * @param OutputPrinter $printer
     * @param string        $longTitle
     */
    private function printDescription(OutputPrinter $printer, $longTitle)
    {
        $lines = explode("\n", $longTitle);
        array_shift($lines);

        foreach ($lines as $line) {
            $printer->writeln(sprintf('%s%s', $this->subIndentText, $line));
        }
    }

    /**
     * Prepends tags string with tag-sign.
     *
     * @param string $tag
     *
     * @return string
     */
    private function prependTagWithTagSign($tag)
    {
        return '@' . $tag;
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
