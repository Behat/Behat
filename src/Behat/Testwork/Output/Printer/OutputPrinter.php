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
    /**
     * @deprecated since 3.1, to be removed in 4.0
     */
    const VERBOSITY_NORMAL       = 1;
    /**
     * @deprecated since 3.1, to be removed in 4.0
     */
    const VERBOSITY_VERBOSE      = 2;
    /**
     * @deprecated since 3.1, to be removed in 4.0
     */
    const VERBOSITY_VERY_VERBOSE = 3;
    /**
     * @deprecated since 3.1, to be removed in 4.0
     */
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
     *
     * @deprecated since 3.1, to be removed in 4.0
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
     *
     * @deprecated since 3.1, to be removed in 4.0
     */
    public function getOutputStyles();

    /**
     * Forces output to be decorated.
     *
     * @param bool $decorated
     */
    public function setOutputDecorated($decorated);

    /**
     * Returns output decoration status.
     *
     * @return null|Boolean
     *
     * @deprecated since 3.1, to be removed in 4.0
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
     *
     * @deprecated since 3.1, to be removed in 4.0
     */
    public function getOutputVerbosity();

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
