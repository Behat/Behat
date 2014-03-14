<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Behat\Output\Statistics\Statistics;
use Behat\Testwork\Output\Formatter;

/**
 * Prints exercise statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface StatisticsPrinter
{
    /**
     * Prints test suite statistics after run.
     *
     * @param Formatter  $formatter
     * @param Statistics $statistics
     */
    public function printStatistics(Formatter $formatter, Statistics $statistics);
}
