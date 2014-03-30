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
     * @var HookStat[]
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
     * Registers hook stat.
     *
     * @param HookStat $stat
     */
    public function registerHookStat(HookStat $stat)
    {
        $this->hookStats[] = $stat;
    }

    /**
     * Returns counters for different scenario result codes.
     *
     * @return array[]
     */
    public function getScenarioStatCounts()
    {
        return array_map(
            function (array $stats) {
                return count($stats);
            }, $this->scenarioStats
        );
    }

    /**
     * Returns skipped scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function getSkippedScenarios()
    {
        return $this->scenarioStats[TestResult::SKIPPED];
    }

    /**
     * Returns failed scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function getFailedScenarios()
    {
        return $this->scenarioStats[TestResult::FAILED];
    }

    /**
     * Returns counters for different step result codes.
     *
     * @return array[]
     */
    public function getStepStatCounts()
    {
        return array_map(
            function (array $stats) {
                return count($stats);
            }, $this->stepStats
        );
    }

    /**
     * Returns failed step stats.
     *
     * @return StepStat[]
     */
    public function getFailedSteps()
    {
        return $this->stepStats[TestResult::FAILED];
    }

    /**
     * Returns pending step stats.
     *
     * @return StepStat[]
     */
    public function getPendingSteps()
    {
        return $this->stepStats[TestResult::PENDING];
    }

    /**
     * Returns failed hook stats.
     *
     * @return HookStat[]
     */
    public function getFailedHookStats()
    {
        return array_filter(
            $this->hookStats, function (HookStat $stat) {
                return !$stat->isSuccessful();
            }
        );
    }
}
