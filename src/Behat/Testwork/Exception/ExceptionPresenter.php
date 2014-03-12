<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Exception;

use Behat\Testwork\Exception\Stringer\ExceptionStringer;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Exception;

/**
 * Presents exceptions as strings using registered stringers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ExceptionPresenter
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var ExceptionStringer[]
     */
    private $stringers = array();
    /**
     * @var integer
     */
    private $defaultVerbosity = OutputPrinter::VERBOSITY_NORMAL;

    /**
     * Initializes presenter.
     *
     * @param string  $basePath
     * @param integer $defaultVerbosity
     */
    public function __construct($basePath = null, $defaultVerbosity = OutputPrinter::VERBOSITY_NORMAL)
    {
        if (null !== $basePath) {
            $realBasePath = realpath($basePath);

            if ($realBasePath) {
                $basePath = $realBasePath;
            }
        }

        $this->basePath = $basePath;
        $this->defaultVerbosity = $defaultVerbosity;
    }

    /**
     * Registers exception stringer.
     *
     * @param ExceptionStringer $stringer
     */
    public function registerExceptionStringer(ExceptionStringer $stringer)
    {
        $this->stringers[] = $stringer;
    }

    /**
     * Sets default verbosity to a specified level.
     *
     * @param integer $defaultVerbosity
     */
    public function setDefaultVerbosity($defaultVerbosity)
    {
        $this->defaultVerbosity = $defaultVerbosity;
    }

    /**
     * Presents exception as a string.
     *
     * @param Exception $exception
     * @param integer   $verbosity
     *
     * @return string
     */
    public function presentException(Exception $exception, $verbosity = null)
    {
        $verbosity = $verbosity ?: $this->defaultVerbosity;

        foreach ($this->stringers as $stringer) {
            if ($stringer->supportsException($exception)) {
                return $this->relativizePaths($stringer->stringException($exception, $verbosity));
            }
        }

        if (OutputPrinter::VERBOSITY_VERY_VERBOSE <= $verbosity) {
            return $this->relativizePaths(trim($exception));
        }

        return trim($this->relativizePaths($exception->getMessage()) . ' (' . get_class($exception) . ')');
    }

    /**
     * Relativizes absolute paths in the text.
     *
     * @param string $text
     *
     * @return string
     */
    private function relativizePaths($text)
    {
        if (!$this->basePath) {
            return $text;
        }

        return str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $text);
    }
}
