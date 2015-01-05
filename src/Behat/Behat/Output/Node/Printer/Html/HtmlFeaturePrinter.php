<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Html;

use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Behat\Output\Node\Printer\Helper\HtmlPrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints feature header and footer.
 *
 * @author Ali Bahman <abn@webit4.me>
 */
final class HtmlFeaturePrinter extends AbstractHtmlPrinter implements FeaturePrinter
{
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
    public function printHeader(Formatter $formatter, FeatureNode $feature)
    {
        $this->getHtmlPrinter($formatter->getOutputPrinter())->openFeature($feature);
    }

    /**
     * {@inheritdoc}
     */
    public function printFooter(Formatter $formatter, TestResult $result)
    {
        $this->getHtmlPrinter($formatter->getOutputPrinter())->closeFeature($result);
    }

}
