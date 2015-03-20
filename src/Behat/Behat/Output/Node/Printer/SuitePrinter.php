<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Suite\Suite;

/**
 * Prints suite headers and footers.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
interface SuitePrinter
{
    /**
     * Prints suite header using provided formatter.
     *
     * @param Formatter $formatter
     * @param Suite     $suite
     */
    public function printHeader(Formatter $formatter, Suite $suite);

    /**
     * Prints suite footer using provided printer.
     *
     * @param Formatter $formatter
     * @param Suite     $suite
     */
    public function printFooter(Formatter $formatter, Suite $suite);
}
