<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Progress;

use Behat\Behat\Output\Node\Printer\CounterPrinter;
use Behat\Behat\Output\Node\Printer\ListPrinter;
use Behat\Behat\Output\Node\Printer\StatisticsPrinter;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat progress statistics printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ProgressStatisticsPrinter implements StatisticsPrinter
{
    /**
     * @var CounterPrinter
     */
    private $counterPrinter;
    /**
     * @var ListPrinter
     */
    private $listPrinter;

    /**
     * Initializes printer.
     *
     * @param CounterPrinter $counterPrinter
     * @param ListPrinter    $listPrinter
     */
    public function __construct(CounterPrinter $counterPrinter, ListPrinter $listPrinter)
    {
        $this->counterPrinter = $counterPrinter;
        $this->listPrinter = $listPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function printStatistics(Formatter $formatter, Statistics $statistics, Timer $timer, Memory $memory)
    {
        $printer = $formatter->getOutputPrinter();

        $printer->writeln();
        $printer->writeln();

        $scenarioStats = $statistics->getScenarioStatsWithResultCode(TestResult::SKIPPED);
        $this->listPrinter->printScenariosList($printer, 'skipped_scenarios_title', TestResult::SKIPPED, $scenarioStats);

        $scenarioStats = $statistics->getScenarioStatsWithResultCode(TestResult::FAILED);
        $this->listPrinter->printScenariosList($printer, 'failed_scenarios_title', TestResult::FAILED, $scenarioStats);

        $hookStats = $statistics->getFailedHookStats();
        $this->listPrinter->printFailedHooksList($printer, 'failed_hooks_title', $hookStats);

        $stepStats = $statistics->getStepStatsWithResultCode(TestResult::FAILED);
        $this->listPrinter->printStepList($printer, 'failed_steps_title', TestResult::FAILED, $stepStats);

        $stepStats = $statistics->getStepStatsWithResultCode(TestResult::PENDING);
        $this->listPrinter->printStepList($printer, 'pending_steps_title', TestResult::PENDING, $stepStats);

        $this->counterPrinter->printCounters($printer, 'scenarios_count', $statistics->getScenarioStats());
        $this->counterPrinter->printCounters($printer, 'steps_count', $statistics->getStepStats());

        $formatter->getOutputPrinter()->writeln(sprintf('%s (%s)', $timer, $memory));
    }
}
