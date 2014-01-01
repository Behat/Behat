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
 * Testwork printer interface.
 *
 * Isolates all console/filesystem writing.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
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
     * Sets output to be verbose.
     *
     * @param Boolean $verbose
     */
    public function setVerbose($verbose = true);

    /**
     * Checks if output is verbose.
     *
     * @return Boolean
     */
    public function isVerbose();

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
