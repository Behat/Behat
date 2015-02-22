<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Html;

use Behat\Behat\Output\Node\Printer\CounterPrinter;
use Behat\Behat\Output\Node\Printer\Helper\HtmlPrinter;
use Behat\Behat\Output\Node\Printer\ListPrinter;
use Behat\Behat\Output\Node\Printer\StatisticsPrinter;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;

/**
 * Prints exercise statistics.
 *
 * @author Ali Bahman <abn@webit4.me>
 */
final class HtmlStatisticsPrinter implements StatisticsPrinter
{
    /**
     * @var HtmlPrinter
     */
    private $htmlPrinter;

    /**
     * @var CounterPrinter
     */
    private $counterPrinter;
    /**
     * @var ListPrinter
     */
    private $listPrinter;

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
    public function printStatistics(Formatter $formatter, Statistics $statistics)
    {
        $this->getHtmlPrinter($formatter->getOutputPrinter())->addNavigator($statistics);
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
