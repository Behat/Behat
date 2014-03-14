<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Statistics;

use Behat\Behat\Tester\Result\StepResult;
use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Collects and provided exercise statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Statistics
{
    /**
     * @var Timer
     */
    private $timer;
    /**
     * @var Memory
     */
    private $memory;
    /**
     * @var ScenarioStat[]
     */
    private $scenarioStats = array();
    /**
     * @var StepStat[]
     */
    private $stepStats = array();
    /**
     * @var FailedHookStat[]
     */
    private $hookStats = array();

    /**
     * Initializes statistics.
     */
    public function __construct()
    {
        $this->scenarioStats = $this->stepStats = array(
            TestResult::PASSED    => array(),
            TestResult::FAILED    => array(),
            StepResult::UNDEFINED => array(),
            TestResult::PENDING   => array(),
            TestResult::SKIPPED   => array()
        );

        $this->timer = new Timer();
        $this->memory = new Memory();
    }

    /**
     * Starts timer.
     */
    public function startTimer()
    {
        $this->timer->start();
    }

    /**
     * Stops timer.
     */
    public function stopTimer()
    {
        $this->timer->stop();
    }

    /**
     * Returns timer object.
     *
     * @return Timer
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * Returns memory usage object.
     *
     * @return Memory
     */
    public function getMemory()
    {
        return $this->memory;
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
     * Registers failed hook stat.
     *
     * @param FailedHookStat $stat
     */
    public function registerFailedHookStat(FailedHookStat $stat)
    {
        $this->hookStats[] = $stat;
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

    /**
     * Returns failed hook stats.
     *
     * @return FailedHookStat[]
     */
    public function getFailedHookStats()
    {
        return $this->hookStats;
    }
}
