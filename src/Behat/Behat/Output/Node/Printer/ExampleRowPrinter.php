<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Output\Formatter;

/**
 * Prints outline example row results.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ExampleRowPrinter
{
    /**
     * Prints example row result using provided printer.
     *
     * @param Formatter         $formatter
     * @param OutlineNode       $outline
     * @param ExampleNode       $example
     * @param AfterStepTested[] $events
     */
    public function printExampleRow(Formatter $formatter, OutlineNode $outline, ExampleNode $example, array $events);
}
