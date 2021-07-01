<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer\Factory;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class OutputFactory
{
    public const VERBOSITY_NORMAL       = 1;
    public const VERBOSITY_VERBOSE      = 2;
    public const VERBOSITY_VERY_VERBOSE = 3;
    public const VERBOSITY_DEBUG        = 4;

    /**
     * @var null|string
     */
    private $outputPath;
    /**
     * @var array
     */
    private $outputStyles = array();
    /**
     * @var null|bool
     */
    private $outputDecorated = null;
    /**
     * @var integer
     */
    private $verbosityLevel = 0;

    /**
     * Sets output path.
     *
     * @param string $path
     */
    public function setOutputPath($path)
    {
        $this->outputPath = $path;
    }

    /**
     * Returns output path.
     *
     * @return null|string
     */
    public function getOutputPath()
    {
        return $this->outputPath;
    }

    /**
     * Sets output styles.
     *
     * @param array $styles
     */
    public function setOutputStyles(array $styles)
    {
        $this->outputStyles = $styles;
    }

    /**
     * Returns output styles.
     *
     * @return array
     */
    public function getOutputStyles()
    {
        return $this->outputStyles;
    }

    /**
     * Forces output to be decorated.
     *
     * @param bool $decorated
     */
    public function setOutputDecorated($decorated)
    {
        $this->outputDecorated = $decorated;
    }

    /**
     * Returns output decoration status.
     *
     * @return null|bool
     */
    public function isOutputDecorated()
    {
        return $this->outputDecorated;
    }

    /**
     * Sets output verbosity level.
     *
     * @param integer $level
     */
    public function setOutputVerbosity($level)
    {
        $this->verbosityLevel = intval($level);
    }

    /**
     * Returns output verbosity level.
     *
     * @return integer
     */
    public function getOutputVerbosity()
    {
        return $this->verbosityLevel;
    }

    /**
     * Returns a new output stream.
     *
     * @return OutputInterface
     */
    abstract public function createOutput();
}
