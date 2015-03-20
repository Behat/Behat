<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Statistics;

use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Counter\Memory;

/**
 * A TotalStatistics decorator to get statistics per phase.
 *
 * This is useful to show the amount of failures
 * in a single suite for instance.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class PhaseStatistics implements Statistics
{
    /**
     * @var TotalStatistics
     */
    private $statistics;

    public function __construct()
    {
        $this->statistics = new TotalStatistics();
    }

    /**
     * Resets the statistics.
     */
    public function reset()
    {
        $this->statistics = new TotalStatistics();
    }

    /**
     * Starts timer.
     */
    public function startTimer()
    {
        $this->statistics->startTimer();
    }

    /**
     * Stops timer.
     */
    public function stopTimer()
    {
        $this->statistics->stopTimer();
    }

    /**
     * Returns timer object.
     *
     * @return Timer
     */
    public function getTimer()
    {
        return $this->statistics->getTimer();
    }

    /**
     * Returns memory usage object.
     *
     * @return Memory
     */
    public function getMemory()
    {
        return $this->statistics->getMemory();
    }

    /**
     * Registers scenario stat.
     *
     * @param ScenarioStat $stat
     */
    public function registerScenarioStat(ScenarioStat $stat)
    {
        $this->statistics->registerScenarioStat($stat);
    }

    /**
     * Registers step stat.
     *
     * @param StepStat $stat
     */
    public function registerStepStat(StepStat $stat)
    {
        $this->statistics->registerStepStat($stat);
    }

    /**
     * Registers hook stat.
     *
     * @param HookStat $stat
     */
    public function registerHookStat(HookStat $stat)
    {
        $this->statistics->registerHookStat($stat);
    }

    /**
     * Returns counters for different scenario result codes.
     *
     * @return array[]
     */
    public function getScenarioStatCounts()
    {
        return $this->statistics->getScenarioStatCounts();
    }

    /**
     * Returns skipped scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function getSkippedScenarios()
    {
        return $this->statistics->getSkippedScenarios();
    }

    /**
     * Returns failed scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function getFailedScenarios()
    {
        return $this->statistics->getFailedScenarios();
    }

    /**
     * Returns counters for different step result codes.
     *
     * @return array[]
     */
    public function getStepStatCounts()
    {
        return $this->statistics->getStepStatCounts();
    }

    /**
     * Returns failed step stats.
     *
     * @return StepStat[]
     */
    public function getFailedSteps()
    {
        return $this->statistics->getFailedSteps();
    }

    /**
     * Returns pending step stats.
     *
     * @return StepStat[]
     */
    public function getPendingSteps()
    {
        return $this->statistics->getPendingSteps();
    }

    /**
     * Returns failed hook stats.
     *
     * @return HookStat[]
     */
    public function getFailedHookStats()
    {
        return $this->statistics->getFailedHookStats();
    }
}
