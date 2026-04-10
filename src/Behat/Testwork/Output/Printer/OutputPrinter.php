<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer;

/**
 * Isolates all console/filesystem writing.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
interface OutputPrinter
{
    /**
     * Sets output path.
     */
    public function setOutputPath(string $path): void;

    /**
     * Sets output styles.
     */
    public function setOutputStyles(array $styles): void;

    /**
     * Forces output to be decorated.
     */
    public function setOutputDecorated(bool $decorated): void;

    /**
     * Sets output verbosity level.
     */
    public function setOutputVerbosity(int $level): void;

    /**
     * Writes message(s) to output stream.
     *
     * @param string|array $messages message or array of messages
     */
    public function write(string|array $messages): void;

    /**
     * Writes newlined message(s) to output stream.
     *
     * @param string|array $messages message or array of messages
     */
    public function writeln(string|array $messages = ''): void;

    /**
     * Clear output stream, so on next write formatter will need to init (create) it again.
     */
    public function flush(): void;
}
