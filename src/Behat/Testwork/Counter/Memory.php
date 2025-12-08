<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Counter;

use Stringable;

/**
 * Counts amount of system memory being used.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Memory implements Stringable
{
    /**
     * @var string[]
     */
    private $units = ['B', 'Kb', 'Mb', 'Gb', 'Tb'];

    /**
     * Returns current memory usage.
     */
    public function getMemoryUsage(): int
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
     * @param int $bytes
     */
    private function humanize($bytes): string
    {
        $e = intval(floor(log($bytes) / log(1024)));

        if (!isset($this->units[$e])) {
            return 'Can not calculate memory usage';
        }

        return sprintf('%.2f%s', $bytes / 1024 ** floor($e), $this->units[$e]);
    }
}
