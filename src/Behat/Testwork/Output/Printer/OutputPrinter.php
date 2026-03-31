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
     *
     * @param string $path
     */
    public function setOutputPath($path);

    /**
     * Sets output styles.
     */
    public function setOutputStyles(array $styles);

    /**
     * Forces output to be decorated.
     *
     * @param bool $decorated
     */
    public function setOutputDecorated($decorated);

    /**
     * Sets output verbosity level.
     *
     * @param int $level
     */
    public function setOutputVerbosity($level);

    /**
     * Writes message(s) to output stream.
     *
     * @param string|array $messages message or array of messages
     */
    public function write($messages);

    /**
     * Writes newlined message(s) to output stream.
     *
     * @param string|array $messages message or array of messages
     */
    public function writeln($messages = '');

    /**
     * Clear output stream, so on next write formatter will need to init (create) it again.
     */
    public function flush();
}
