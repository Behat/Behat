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
use Behat\Testwork\Tester\Result\TestResults;

/**
 * Collects and provided exercise statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
final class TotalStatistics implements Statistics
{
    private readonly Timer $timer;
    private readonly Memory $memory;
    /**
     * @var array<TestResult::*, int>
     */
    private array $scenarioCounters = [];
    /**
     * @var array<StepResult::*, int>
     */
    private array $stepCounters = [];
    /**
     * @var list<ScenarioStat>
     */
    private array $failedScenarioStats = [];
    /**
     * @var list<ScenarioStat>
     */
    private array $skippedScenarioStats = [];
    /**
     * @var list<StepStatV2>
     */
    private array $failedStepStats = [];
    /**
     * @var list<StepStatV2>
     */
    private array $pendingStepStats = [];
    /**
     * @var list<HookStat>
     */
    private array $failedHookStats = [];

    /**
     * Initializes statistics.
     */
    public function __construct()
    {
        $this->resetAllCounters();

        $this->timer = new Timer();
        $this->memory = new Memory();
    }

    public function resetAllCounters(): void
    {
        $this->scenarioCounters = $this->stepCounters = [
            TestResult::PASSED => 0,
            TestResult::FAILED => 0,
            TestResult::UNDEFINED => 0,
            TestResult::PENDING => 0,
            TestResult::SKIPPED => 0,
        ];
    }

    /**
     * Starts timer.
     */
    public function startTimer(): void
    {
        $this->timer->start();
    }

    /**
     * Stops timer.
     */
    public function stopTimer(): void
    {
        $this->timer->stop();
    }

    /**
     * Returns timer object.
     */
    public function getTimer(): Timer
    {
        return $this->timer;
    }

    /**
     * Returns memory usage object.
     */
    public function getMemory(): Memory
    {
        return $this->memory;
    }

    /**
     * Registers scenario stat.
     */
    public function registerScenarioStat(ScenarioStat $stat): void
    {
        if (TestResults::NO_TESTS === $stat->getResultCode()) {
            return;
        }

        ++$this->scenarioCounters[$stat->getResultCode()];

        if (TestResult::FAILED === $stat->getResultCode()) {
            $this->failedScenarioStats[] = $stat;
        }

        if (TestResult::SKIPPED === $stat->getResultCode()) {
            $this->skippedScenarioStats[] = $stat;
        }
    }

    /**
     * Registers step stat.
     */
    public function registerStepStat(StepStatV2 $stat): void
    {
        ++$this->stepCounters[$stat->getResultCode()];

        if (TestResult::FAILED === $stat->getResultCode()) {
            $this->failedStepStats[] = $stat;
        }

        if (TestResult::PENDING === $stat->getResultCode()) {
            $this->pendingStepStats[] = $stat;
        }
    }

    /**
     * Registers hook stat.
     */
    public function registerHookStat(HookStat $stat): void
    {
        if ($stat->isSuccessful()) {
            return;
        }

        $this->failedHookStats[] = $stat;
    }

    /**
     * Returns counters for different scenario result codes.
     *
     * @return array<TestResult::*, int>
     */
    public function getScenarioStatCounts(): array
    {
        return $this->scenarioCounters;
    }

    /**
     * Returns skipped scenario stats.
     *
     * @return list<ScenarioStat>
     */
    public function getSkippedScenarios(): array
    {
        return $this->skippedScenarioStats;
    }

    /**
     * Returns failed scenario stats.
     *
     * @return list<ScenarioStat>
     */
    public function getFailedScenarios(): array
    {
        return $this->failedScenarioStats;
    }

    /**
     * Returns counters for different step result codes.
     *
     * @return array<StepResult::*, int>
     */
    public function getStepStatCounts(): array
    {
        return $this->stepCounters;
    }

    /**
     * Returns failed step stats.
     *
     * @return list<StepStatV2>
     */
    public function getFailedSteps(): array
    {
        return $this->failedStepStats;
    }

    /**
     * Returns pending step stats.
     *
     * @return list<StepStatV2>
     */
    public function getPendingSteps(): array
    {
        return $this->pendingStepStats;
    }

    /**
     * Returns failed hook stats.
     *
     * @return list<HookStat>
     */
    public function getFailedHookStats(): array
    {
        return $this->failedHookStats;
    }
}
