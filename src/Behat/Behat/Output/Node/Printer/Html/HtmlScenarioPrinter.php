<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Html;

use Behat\Behat\Output\Node\Printer\Helper\HtmlPrinter;
use Behat\Behat\Output\Node\Printer\ScenarioPrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints scenario headers (with tags, keyword and long title) and footers.
 *
 * @author Ali Bahman <abn@webit4.me>
 */
final class HtmlScenarioPrinter implements ScenarioPrinter
{
    /**
     * @var HtmlPrinter
     */
    private $htmlPrinter;

    /**
     * @param HtmlPrinter $htmlPrinter
     */
    public function __construct(HtmlPrinter $htmlPrinter)
    {
        $this->setHtmlPrinter($htmlPrinter);
    }

    /**
     * {@inheritdoc}
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature, Scenario $scenario)
    {
        $this->getHtmlPrinter($formatter->getOutputPrinter())->openScenario($scenario);
    }

    /**
     * {@inheritdoc}
     */
    public function printFooter(Formatter $formatter, TestResult $result)
    {
        $this->getHtmlPrinter($formatter->getOutputPrinter())
            ->closeScenario($result);
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
