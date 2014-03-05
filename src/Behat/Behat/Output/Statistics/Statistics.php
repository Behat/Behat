<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Statistics;

use Behat\Behat\Tester\Result\BehatTestResult;

/**
 * Behat statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Statistics
{
    /**
     * @var ScenarioStat[]
     */
    private $scenarioStats = array();
    /**
     * @var StepStat[]
     */
    private $stepStats = array();

    /**
     * Initializes statistics.
     */
    public function __construct()
    {
        $this->scenarioStats = $this->stepStats = array(
            BehatTestResult::PASSED    => array(),
            BehatTestResult::FAILED    => array(),
            BehatTestResult::UNDEFINED => array(),
            BehatTestResult::PENDING   => array(),
            BehatTestResult::SKIPPED   => array()
        );
    }

    /**
     * Registers scenario stat.
     *
     * @param ScenarioStat $stat
     */
    public function registerScenarioStat(ScenarioStat $stat)
    {
        $this->scenarioStats[$stat->getResultCode()][] = $stat;
    }

    /**
     * Registers step stat.
     *
     * @param StepStat $stat
     */
    public function registerStepStat(StepStat $stat)
    {
        $this->stepStats[$stat->getResultCode()][] = $stat;
    }

    /**
     * Returns all captured scenario stats associated by result code.
     *
     * @return ScenarioStat[]
     */
    public function getScenarioStats()
    {
        return $this->scenarioStats;
    }

    /**
     * Returns all scenario stats matching provided result code.
     *
     * @param integer $resultCode
     *
     * @return ScenarioStat[]
     */
    public function getScenarioStatsWithResultCode($resultCode)
    {
        return $this->scenarioStats[$resultCode];
    }

    /**
     * Returns all captured step stats associated by result code.
     *
     * @return StepStat[]
     */
    public function getStepStats()
    {
        return $this->stepStats;
    }

    /**
     * Returns all step stats matching provided result code.
     *
     * @param integer $resultCode
     *
     * @return StepStat[]
     */
    public function getStepStatsWithResultCode($resultCode)
    {
        return $this->stepStats[$resultCode];
    }
}
