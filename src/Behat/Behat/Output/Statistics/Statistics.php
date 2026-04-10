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
 * @author Wouter J <wouter@wouterj.nl>
 *
 * @api
 */
interface Statistics
{
    /**
     * Starts timer.
     */
    public function startTimer(): void;

    /**
     * Stops timer.
     */
    public function stopTimer(): void;

    /**
     * Returns timer object.
     */
    public function getTimer(): Timer;

    /**
     * Returns memory usage object.
     */
    public function getMemory(): Memory;

    /**
     * Registers scenario stat.
     */
    public function registerScenarioStat(ScenarioStat $stat): void;

    /**
     * Registers step stat.
     */
    public function registerStepStat(StepStatV2 $stat): void;

    /**
     * Registers hook stat.
     */
    public function registerHookStat(HookStat $stat): void;

    /**
     * Returns counters for different scenario result codes.
     *
     * @return array<TestResult::*, int>
     */
    public function getScenarioStatCounts(): array;

    /**
     * Returns skipped scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function getSkippedScenarios(): array;

    /**
     * Returns failed scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function getFailedScenarios(): array;

    /**
     * Returns counters for different step result codes.
     *
     * @return array<StepResult::*, int>
     */
    public function getStepStatCounts(): array;

    /**
     * Returns failed step stats.
     *
     * @return StepStatV2[]
     */
    public function getFailedSteps(): array;

    /**
     * Returns pending step stats.
     *
     * @return StepStatV2[]
     */
    public function getPendingSteps(): array;

    /**
     * Returns failed hook stats.
     *
     * @return HookStat[]
     */
    public function getFailedHookStats(): array;
}
