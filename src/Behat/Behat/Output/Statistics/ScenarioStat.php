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
 * Behat scenario stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ScenarioStat
{
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $path;
    /**
     * @var integer
     */
    private $resultCode;

    /**
     * Initializes scenario stat.
     *
     * @param string  $title
     * @param string  $path
     * @param integer $resultCode
     */
    public function __construct($title, $path, $resultCode)
    {
        $this->title = $title;
        $this->path = $path;
        $this->resultCode = $resultCode;
    }

    /**
     * Returns scenario title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns scenario path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns scenario result code.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->resultCode;
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
