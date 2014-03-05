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
 * Behat step stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepStat
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var integer
     */
    private $resultCode;

    /**
     * Initializes step stat.
     *
     * @param string  $path
     * @param integer $resultCode
     */
    public function __construct($path, $resultCode)
    {
        $this->path = $path;
        $this->resultCode = $resultCode;
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
     * Returns string representation for a stat.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }
}
