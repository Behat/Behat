<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat pretty feature printer.
 *
 * Prints feature header and footer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyFeaturePrinter implements FeaturePrinter
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
     * @param integer $indentation
     * @param integer $subIndentation
     */
    public function __construct($indentation = 0, $subIndentation = 2)
    {
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->subIndentText = $this->indentText . str_repeat(' ', intval($subIndentation));
    }

    /**
     * {@inheritdoc}
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature)
    {
        $this->printTitle($formatter->getOutputPrinter(), $feature);
        $this->printDescription($formatter->getOutputPrinter(), $feature);
    }

    /**
     * {@inheritdoc}
     */
    public function printFooter(Formatter $formatter, FeatureNode $feature, TestResult $result)
    {
    }

    /**
     * Prints feature title using provided printer.
     *
     * @param OutputPrinter $printer
     * @param FeatureNode   $feature
     */
    private function printTitle(OutputPrinter $printer, FeatureNode $feature)
    {
        $printer->write(sprintf('%s{+keyword}%s:{-keyword}', $this->indentText, $feature->getKeyword()));

        if ($title = $feature->getTitle()) {
            $printer->write(sprintf(' %s', $title));
        }

        $printer->writeln();
    }

    /**
     * Prints feature description using provided printer.
     *
     * @param OutputPrinter $printer
     * @param FeatureNode   $feature
     */
    private function printDescription(OutputPrinter $printer, FeatureNode $feature)
    {
        foreach (explode("\n", $feature->getDescription()) as $descriptionLine) {
            $printer->writeln(sprintf('%s%s', $this->subIndentText, $descriptionLine));
        }

        $printer->writeln();
    }
}
