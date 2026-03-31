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
 * A TotalStatistics decorator to get statistics per phase.
 *
 * This is useful to show the amount of failures
 * in a single suite for instance.
 *
 * @author Wouter J <wouter@wouterj.nl>
 *
 * @api
 */
final class PhaseStatistics implements Statistics
{
    private TotalStatistics $statistics;

    public function __construct()
    {
        $this->statistics = new TotalStatistics();
    }

    /**
     * Resets the statistics.
     */
    public function reset(): void
    {
        $this->statistics = new TotalStatistics();
    }

    /**
     * Starts timer.
     */
    public function startTimer(): void
    {
        $this->statistics->startTimer();
    }

    /**
     * Stops timer.
     */
    public function stopTimer(): void
    {
        $this->statistics->stopTimer();
    }

    /**
     * Returns timer object.
     */
    public function getTimer(): Timer
    {
        return $this->statistics->getTimer();
    }

    /**
     * Returns memory usage object.
     */
    public function getMemory(): Memory
    {
        return $this->statistics->getMemory();
    }

    /**
     * Registers scenario stat.
     */
    public function registerScenarioStat(ScenarioStat $stat): void
    {
        $this->statistics->registerScenarioStat($stat);
    }

    /**
     * Registers step stat.
     */
    public function registerStepStat(StepStatV2 $stat): void
    {
        $this->statistics->registerStepStat($stat);
    }

    /**
     * Registers hook stat.
     */
    public function registerHookStat(HookStat $stat): void
    {
        $this->statistics->registerHookStat($stat);
    }

    /**
     * Returns counters for different scenario result codes.
     *
     * @return array<TestResult::*, int>
     */
    public function getScenarioStatCounts(): array
    {
        return $this->statistics->getScenarioStatCounts();
    }

    /**
     * Returns skipped scenario stats.
     *
     * @return list<ScenarioStat>
     */
    public function getSkippedScenarios(): array
    {
        return $this->statistics->getSkippedScenarios();
    }

    /**
     * Returns failed scenario stats.
     *
     * @return list<ScenarioStat>
     */
    public function getFailedScenarios(): array
    {
        return $this->statistics->getFailedScenarios();
    }

    /**
     * Returns counters for different step result codes.
     *
     * @return array<StepResult::*, int>
     */
    public function getStepStatCounts(): array
    {
        return $this->statistics->getStepStatCounts();
    }

    /**
     * Returns failed step stats.
     *
     * @return list<StepStatV2>
     */
    public function getFailedSteps(): array
    {
        return $this->statistics->getFailedSteps();
    }

    /**
     * Returns pending step stats.
     *
     * @return list<StepStatV2>
     */
    public function getPendingSteps(): array
    {
        return $this->statistics->getPendingSteps();
    }

    /**
     * Returns failed hook stats.
     *
     * @return list<HookStat>
     */
    public function getFailedHookStats(): array
    {
        return $this->statistics->getFailedHookStats();
    }
}
