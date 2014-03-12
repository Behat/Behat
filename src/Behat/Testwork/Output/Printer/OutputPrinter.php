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
 */
interface OutputPrinter
{
    const VERBOSITY_NORMAL       = 1;
    const VERBOSITY_VERBOSE      = 2;
    const VERBOSITY_VERY_VERBOSE = 3;
    const VERBOSITY_DEBUG        = 4;

    /**
     * Sets output path.
     *
     * @param string $path
     */
    public function setOutputPath($path);

    /**
     * Returns output path.
     *
     * @return null|string
     */
    public function getOutputPath();

    /**
     * Sets output styles.
     *
     * @param array $styles
     */
    public function setOutputStyles(array $styles);

    /**
     * Returns output styles.
     *
     * @return array
     */
    public function getOutputStyles();

    /**
     * Forces output to be decorated.
     *
     * @param Boolean $decorated
     */
    public function setOutputDecorated($decorated);

    /**
     * Returns output decoration status.
     *
     * @return null|Boolean
     */
    public function isOutputDecorated();

    /**
     * Sets output verbosity level.
     *
     * @param integer $level
     */
    public function setOutputVerbosity($level);

    /**
     * Returns output verbosity level.
     *
     * @return integer
     */
    public function getOutputVerbosity();

    /**
     * Writes message(s) to output console.
     *
     * @param string|array $messages message or array of messages
     */
    public function write($messages);

    /**
     * Writes newlined message(s) to output console.
     *
     * @param string|array $messages message or array of messages
     */
    public function writeln($messages = '');

    /**
     * Clear output console, so on next write formatter will need to init (create) it again.
     */
    public function flush();
}
