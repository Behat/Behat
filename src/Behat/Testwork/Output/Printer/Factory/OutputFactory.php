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
    public const VERBOSITY_NORMAL = 1;
    public const VERBOSITY_VERBOSE = 2;
    public const VERBOSITY_VERY_VERBOSE = 3;
    public const VERBOSITY_DEBUG = 4;

    private ?string $outputPath = null;
    private array $outputStyles = [];

    private ?bool $outputDecorated = null;
    private int $verbosityLevel = 0;

    /**
     * Sets output path.
     */
    public function setOutputPath(string $path): void
    {
        $this->outputPath = $path;
    }

    /**
     * Returns output path.
     */
    public function getOutputPath(): ?string
    {
        return $this->outputPath;
    }

    /**
     * Sets output styles.
     */
    public function setOutputStyles(array $styles): void
    {
        $this->outputStyles = $styles;
    }

    /**
     * Returns output styles.
     */
    public function getOutputStyles(): array
    {
        return $this->outputStyles;
    }

    /**
     * Forces output to be decorated.
     */
    public function setOutputDecorated(?bool $decorated): void
    {
        $this->outputDecorated = $decorated;
    }

    /**
     * Returns output decoration status.
     */
    public function isOutputDecorated(): ?bool
    {
        return $this->outputDecorated;
    }

    /**
     * Sets output verbosity level.
     */
    public function setOutputVerbosity(int $level): void
    {
        $this->verbosityLevel = intval($level);
    }

    /**
     * Returns output verbosity level.
     */
    public function getOutputVerbosity(): int
    {
        return $this->verbosityLevel;
    }

    /**
     * Returns a new output stream.
     */
    abstract public function createOutput(): OutputInterface;
}
