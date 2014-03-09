<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Statistics;

use Exception;

/**
 * Behat step stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StepStat
{
    /**
     * @var string
     */
    private $text;
    /**
     * @var string
     */
    private $path;
    /**
     * @var integer
     */
    private $resultCode;
    /**
     * @var null|Exception
     */
    private $exception;
    /**
     * @var null|string
     */
    private $stdOut;

    /**
     * Initializes step stat.
     *
     * @param string         $text
     * @param string         $path
     * @param integer        $resultCode
     * @param null|Exception $exception
     * @param null|string    $stdOut
     */
    public function __construct($text, $path, $resultCode, Exception $exception = null, $stdOut = null)
    {
        $this->text = $text;
        $this->path = $path;
        $this->resultCode = $resultCode;
        $this->exception = $exception;
        $this->stdOut = $stdOut;
    }

    /**
     * Returns step text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Returns step path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns step result code.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * Returns step exception (if has one).
     *
     * @return null|Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Returns step output (if has one).
     *
     * @return null|string
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }

    /**
     * Returns string representation for a stat.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }
}
