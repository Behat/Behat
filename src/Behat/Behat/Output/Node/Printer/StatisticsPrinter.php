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
use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Output\Formatter;

/**
 * Behat statistics printer interface.
 *
 * Responsible to print exercise statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface StatisticsPrinter
{
    public function printStatistics(Formatter $formatter, Statistics $statistics, Timer $timer, Memory $memory);
}
