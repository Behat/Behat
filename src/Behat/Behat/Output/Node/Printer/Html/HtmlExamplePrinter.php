<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Html;

use Behat\Behat\Output\Node\Printer\ExamplePrinter;
use Behat\Behat\Output\Node\Printer\Helper\HtmlPrinter;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints example header (usually simply an example row) and footer.
 *
 * @author Ali Bahman <abn@webit4.me>
 */
final class HtmlExamplePrinter implements ExamplePrinter
{
    /**
     * @var HtmlPrinter
     */
    private $htmlPrinter;

    /**
     * Initializes printer.
     *
     * @param HtmlPrinter $htmlPrinter
     */
    public function __construct(HtmlPrinter $htmlPrinter)
    {
        $this->setHtmlPrinter($htmlPrinter);
    }

    /**
     * {@inheritdoc}
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature, ExampleNode $example)
    {
        $this->printTitle($formatter->getOutputPrinter(), $example);
    }

    /**
     * {@inheritdoc}
     */
    public function printFooter(Formatter $formatter, TestResult $result)
    {
    }

    /**
     * Prints example title.
     *
     * @param OutputPrinter $printer
     * @param ExampleNode $example
     */
    private function printTitle(OutputPrinter $printer, ExampleNode $example)
    {
        $printer->write(sprintf('%s', $example->getTitle()));
    }

    /**
     * @param OutputPrinter $printer
     * @return HtmlPrinter
     */
    protected function getHtmlPrinter(OutputPrinter $printer)
    {
        $this->htmlPrinter->setOutputPrinter($printer);
        return $this->htmlPrinter;
    }

    /**
     * @param HtmlPrinter $htmlPrinter
     * @return $this
     */
    protected function setHtmlPrinter(HtmlPrinter $htmlPrinter)
    {
        $this->htmlPrinter = $htmlPrinter;
        return $this;
    }
}
