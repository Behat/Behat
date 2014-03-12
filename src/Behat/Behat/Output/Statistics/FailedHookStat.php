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
 * Behat hook stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FailedHookStat
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $path;
    /**
     * @var Exception
     */
    private $error;
    /**
     * @var string
     */
    private $stdOut;

    /**
     * Initializes hook stat.
     *
     * @param string      $name
     * @param string      $path
     * @param string      $error
     * @param null|string $stdOut
     */
    public function __construct($name, $path, $error, $stdOut = null)
    {
        $this->name = $name;
        $this->path = $path;
        $this->error = $error;
        $this->stdOut = $stdOut;
    }

    /**
     * Returns hook name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns hook standard output (if has some).
     *
     * @return null|string
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }

    /**
     * Returns hook exception.
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns hook path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
