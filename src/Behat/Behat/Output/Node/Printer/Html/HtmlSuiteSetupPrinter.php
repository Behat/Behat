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
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Testwork\Hook\Tester\Setup\HookedSetup;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prints hooks in a pretty fashion.
 *
 * @author Ali Bahman <abn@webit4.me>
 */
final class HtmlSuiteSetupPrinter extends AbstractHtmlPrinter implements SetupPrinter
{
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
    public function printSetup(Formatter $formatter, Setup $setup)
    {
        if (!$setup instanceof HookedSetup) {
            return;
        }

        $this->getHtmlPrinter($formatter->getOutputPrinter())->printHtmlHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function printTeardown(Formatter $formatter, Teardown $teardown)
    {
        if (!$teardown instanceof HookedTeardown) {
            return;
        }

        $this->getHtmlPrinter($formatter->getOutputPrinter())->printHtmlFooter();
    }
}
