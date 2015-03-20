<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Statistics;

use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;


/**
 * Collects and provided exercise statistics.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
interface Statistics
{
    /**
     * Starts timer.
     */
    public function startTimer();

    /**
     * Stops timer.
     */
    public function stopTimer();

    /**
     * Returns timer object.
     *
     * @return Timer
     */
    public function getTimer();

    /**
     * Returns memory usage object.
     *
     * @return Memory
     */
    public function getMemory();

    /**
     * Registers scenario stat.
     *
     * @param ScenarioStat $stat
     */
    public function registerScenarioStat(ScenarioStat $stat);

    /**
     * Registers step stat.
     *
     * @param StepStat $stat
     */
    public function registerStepStat(StepStat $stat);

    /**
     * Registers hook stat.
     *
     * @param HookStat $stat
     */
    public function registerHookStat(HookStat $stat);

    /**
     * Returns counters for different scenario result codes.
     *
     * @return array[]
     */
    public function getScenarioStatCounts();

    /**
     * Returns skipped scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function getSkippedScenarios();

    /**
     * Returns failed scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function getFailedScenarios();

    /**
     * Returns counters for different step result codes.
     *
     * @return array[]
     */
    public function getStepStatCounts();

    /**
     * Returns failed step stats.
     *
     * @return StepStat[]
     */
    public function getFailedSteps();

    /**
     * Returns pending step stats.
     *
     * @return StepStat[]
     */
    public function getPendingSteps();

    /**
     * Returns failed hook stats.
     *
     * @return HookStat[]
     */
    public function getFailedHookStats();
}
