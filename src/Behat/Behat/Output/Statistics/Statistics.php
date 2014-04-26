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
     * @var array
     */
    private $scenarioCounters = array();
    /**
     * @var array
     */
    private $stepCounters = array();
    /**
     * @var ScenarioStat[]
     */
    private $failedScenarioStats = array();
    /**
     * @var ScenarioStat[]
     */
    private $skippedScenarioStats = array();
    /**
     * @var StepStat[]
     */
    private $failedStepStats = array();
    /**
     * @var StepStat[]
     */
    private $pendingStepStats = array();
    /**
     * @var HookStat[]
     */
    private $failedHookStats = array();

    /**
     * Initializes statistics.
     */
    public function __construct()
    {
        $this->scenarioCounters = $this->stepCounters = array(
            TestResult::PASSED    => 0,
            TestResult::FAILED    => 0,
            StepResult::UNDEFINED => 0,
            TestResult::PENDING   => 0,
            TestResult::SKIPPED   => 0
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
        if (TestResults::NO_TESTS === $stat->getResultCode()) {
            return;
        }

        $this->scenarioCounters[$stat->getResultCode()]++;

        if (TestResult::FAILED === $stat->getResultCode()) {
            $this->failedScenarioStats[] = $stat;
        }

        if (TestResult::SKIPPED === $stat->getResultCode()) {
            $this->skippedScenarioStats[] = $stat;
        }
    }

    /**
     * Registers step stat.
     *
     * @param StepStat $stat
     */
    public function registerStepStat(StepStat $stat)
    {
        $this->stepCounters[$stat->getResultCode()]++;

        if (TestResult::FAILED === $stat->getResultCode()) {
            $this->failedStepStats[] = $stat;
        }

        if (TestResult::PENDING === $stat->getResultCode()) {
            $this->pendingStepStats[] = $stat;
        }
    }

    /**
     * Registers hook stat.
     *
     * @param HookStat $stat
     */
    public function registerHookStat(HookStat $stat)
    {
        if ($stat->isSuccessful()) {
            return;
        }

        $this->failedHookStats[] = $stat;
    }

    /**
     * Returns counters for different scenario result codes.
     *
     * @return array[]
     */
    public function getScenarioStatCounts()
    {
        return $this->scenarioCounters;
    }

    /**
     * Returns skipped scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function getSkippedScenarios()
    {
        return $this->skippedScenarioStats;
    }

    /**
     * Returns failed scenario stats.
     *
     * @return ScenarioStat[]
     */
    public function getFailedScenarios()
    {
        return $this->failedScenarioStats;
    }

    /**
     * Returns counters for different step result codes.
     *
     * @return array[]
     */
    public function getStepStatCounts()
    {
        return $this->stepCounters;
    }

    /**
     * Returns failed step stats.
     *
     * @return StepStat[]
     */
    public function getFailedSteps()
    {
        return $this->failedStepStats;
    }

    /**
     * Returns pending step stats.
     *
     * @return StepStat[]
     */
    public function getPendingSteps()
    {
        return $this->pendingStepStats;
    }

    /**
     * Returns failed hook stats.
     *
     * @return HookStat[]
     */
    public function getFailedHookStats()
    {
        return $this->failedHookStats;
    }
}
