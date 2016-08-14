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
 * Second iteration of Behat step stat, with a scenario information.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StepStatV2 extends StepStat
{
    /**
     * @var string
     */
    private $scenarioTitle;
    /**
     * @var string
     */
    private $scenarioPath;
    /**
     * @var string
     */
    private $stepText;
    /**
     * @var string
     */
    private $stepPath;
    /**
     * @var integer
     */
    private $resultCode;
    /**
     * @var null|string
     */
    private $error;
    /**
     * @var null|string
     */
    private $stdOut;

    /**
     * Initializes step stat.
     *
     * @param string      $scenarioTitle
     * @param string      $scenarioPath
     * @param string      $stepText
     * @param string      $stepPath
     * @param integer     $resultCode
     * @param null|string $error
     * @param null|string $stdOut
     */
    public function __construct($scenarioTitle, $scenarioPath, $stepText, $stepPath, $resultCode, $error = null, $stdOut = null)
    {
        parent::__construct($stepText, $stepPath, $resultCode, $error, $stdOut);

        $this->scenarioTitle = $scenarioTitle;
        $this->scenarioPath = $scenarioPath;
        $this->stepText = $stepText;
        $this->stepPath = $stepPath;
        $this->resultCode = $resultCode;
        $this->error = $error;
        $this->stdOut = $stdOut;
    }

    /**
     * Returns associated scenario text.
     *
     * @return string
     */
    public function getScenarioText()
    {
        return $this->scenarioTitle;
    }

    /**
     * Returns associated scenario path.
     *
     * @return string
     */
    public function getScenarioPath()
    {
        return $this->scenarioPath;
    }

    /**
     * Returns step text.
     *
     * @return string
     */
    public function getStepText()
    {
        return $this->stepText;
    }

    /**
     * Returns step path.
     *
     * @return string
     */
    public function getStepPath()
    {
        return $this->stepPath;
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
     * Returns step error (if has one).
     *
     * @return null|string
     */
    public function getError()
    {
        return $this->error;
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
