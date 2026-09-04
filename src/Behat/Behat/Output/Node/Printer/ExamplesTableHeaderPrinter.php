<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Testwork\Output\Formatter;

/**
 * Prints the heading of one of an outline's examples tables.
 *
 * Implemented by outline printers that render each examples table separately. A printer that does not implement this
 * is simply not asked to print the headings.
 *
 * @api
 */
interface ExamplesTableHeaderPrinter
{
    /**
     * Prints the header of one of the outline's examples tables using the provided printer.
     */
    public function printExamplesTableHeader(Formatter $formatter, ExampleTableNode $table): void;
}
