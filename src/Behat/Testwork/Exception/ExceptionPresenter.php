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
use Exception;

/**
 * Testwork exception presenter.
 *
 * Presents exceptions as strings using registered stringers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExceptionPresenter
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
     * Initializes presenter.
     *
     * @param string $basePath
     */
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath;
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
     * Presents exception as a string.
     *
     * @param Exception $exception
     * @param Boolean   $verbose
     *
     * @return string
     */
    public function presentException(Exception $exception, $verbose = false)
    {
        foreach ($this->stringers as $stringer) {
            if ($stringer->supportsException($exception)) {
                return $this->relativizePaths($stringer->stringException($exception, $verbose));
            }
        }

        if ($exception instanceof TestworkException) {
            return trim($this->relativizePaths($exception->getMessage()));
        }

        if ($verbose) {
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
