<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Counter;

/**
 * Counts amount of system memory being used.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Memory
{
    /**
     * @var string[]
     */
    private $units = array('B', 'Kb', 'Mb', 'Gb');

    /**
     * Returns current memory usage.
     *
     * @return float
     */
    public function getMemoryUsage()
    {
        return memory_get_usage();
    }

    /**
     * Presents memory usage in human-readable form.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->humanize($this->getMemoryUsage());
    }

    /**
     * Humanizes usage information.
     *
     * @param float $usage
     *
     * @return string
     */
    private function humanize($usage)
    {
        $sub = intval(floor(log($usage, 1024)));

        return @round($usage / pow(1024, $sub), 2) . $this->units[$sub];
    }
}
