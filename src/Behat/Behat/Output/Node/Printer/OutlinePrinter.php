<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints outline headers and footers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface OutlinePrinter
{
    /**
     * Prints outline header using provided printer.
     *
     * @param Formatter   $formatter
     * @param FeatureNode $feature
     * @param OutlineNode $outline
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature, OutlineNode $outline);

    /**
     * Prints outline footer using provided printer.
     *
     * @param Formatter  $formatter
     * @param TestResult $result
     */
    public function printFooter(Formatter $formatter, TestResult $result);
}
