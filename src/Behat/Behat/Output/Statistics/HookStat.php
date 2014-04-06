<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Statistics;

/**
 * Represents hook stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookStat
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
     * @var string|null
     */
    private $error;
    /**
     * @var string|null
     */
    private $stdOut;

    /**
     * Initializes hook stat.
     *
     * @param string      $name
     * @param string      $path
     * @param null|string $error
     * @param null|string $stdOut
     */
    public function __construct($name, $path, $error = null, $stdOut = null)
    {
        $this->name = $name;
        $this->path = $path;
        $this->error = $error;
        $this->stdOut = $stdOut;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return null === $this->error;
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
