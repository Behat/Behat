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
    private $file;
    /**
     * @var integer
     */
    private $line;
    /**
     * @var integer
     */
    private $resultCode;

    /**
     * Initializes scenario stat.
     *
     * @param string  $file
     * @param integer $line
     * @param integer $resultCode
     */
    public function __construct($file, $line, $resultCode)
    {
        $this->file = $file;
        $this->line = $line;
        $this->resultCode = $resultCode;
    }

    /**
     * Returns scenario path.
     *
     * @return string
     */
    public function getPath()
    {
        return sprintf('%s:%d', $this->file, $this->line);
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
