<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\CounterPrinter;
use Behat\Behat\Output\Node\Printer\ListPrinter;
use Behat\Behat\Output\Node\Printer\StatisticsPrinter;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Config\Formatter\ShowOutputOption;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints exercise statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PrettyStatisticsPrinter implements StatisticsPrinter
{
    /**
     * Initializes printer.
     */
    public function __construct(
        private readonly CounterPrinter $counterPrinter,
        private readonly ListPrinter $listPrinter,
    ) {
    }

    public function printStatistics(Formatter $formatter, Statistics $statistics): void
    {
        $printer = $formatter->getOutputPrinter();

        $hookStats = $statistics->getFailedHookStats();
        $this->listPrinter->printFailedHooksList($printer, 'failed_hooks_title', $hookStats, true);

        $shortSummary = $formatter->getParameter('short_summary');
        if ($shortSummary) {
            $scenarioStats = $statistics->getSkippedScenarios();
            $this->listPrinter->printScenariosList($printer, 'skipped_scenarios_title', TestResult::SKIPPED, $scenarioStats);

            $scenarioStats = $statistics->getFailedScenarios();
            $failedStepStats = $statistics->getFailedSteps();
            $this->listPrinter->printScenariosList($printer, 'failed_scenarios_title', TestResult::FAILED, $scenarioStats, $failedStepStats);
        } else {
            $showOutput = $formatter->getParameter(ShowOutputOption::OPTION_NAME);
            $stepStats = $statistics->getFailedSteps();
            $this->listPrinter->printStepList($printer, 'failed_steps_title', TestResult::FAILED, $stepStats, $showOutput);

            $stepStats = $statistics->getPendingSteps();
            $this->listPrinter->printStepList($printer, 'pending_steps_title', TestResult::PENDING, $stepStats, $showOutput);
        }

        $this->counterPrinter->printCounters($printer, 'scenarios_count', $statistics->getScenarioStatCounts());
        $this->counterPrinter->printCounters($printer, 'steps_count', $statistics->getStepStatCounts());

        if ($formatter->getParameter('timer')) {
            $timer = $statistics->getTimer();
            $memory = $statistics->getMemory();

            $formatter->getOutputPrinter()->writeln(sprintf('%s (%s)', $timer, $memory));
        }
    }
}
