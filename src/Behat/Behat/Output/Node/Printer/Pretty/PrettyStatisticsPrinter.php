<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\StatisticsPrinter;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Behat pretty statistics printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyStatisticsPrinter implements StatisticsPrinter
{
    /**
     * @var \Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter
     */
    private $resultConverter;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var string
     */
    private $basePath;

    /**
     * Initializes printer.
     *
     * @param \Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter $resultConverter
     * @param TranslatorInterface     $translator
     * @param string                  $basePath
     */
    public function __construct(ResultToStringConverter $resultConverter, TranslatorInterface $translator, $basePath)
    {
        $this->resultConverter = $resultConverter;
        $this->translator = $translator;
        $this->basePath = $basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function printStatistics(Formatter $formatter, Statistics $statistics, Timer $timer, Memory $memory)
    {
        $printer = $formatter->getOutputPrinter();

        $scenarioStats = $statistics->getScenarioStatsWithResultCode(TestResult::SKIPPED);
        $this->printScenariosList($printer, 'skipped_scenarios_title', TestResult::SKIPPED, $scenarioStats);

        $scenarioStats = $statistics->getScenarioStatsWithResultCode(TestResult::FAILED);
        $this->printScenariosList($printer, 'failed_scenarios_title', TestResult::FAILED, $scenarioStats);

        $this->printCounters($printer, 'scenarios_count', $statistics->getScenarioStats());
        $this->printCounters($printer, 'steps_count', $statistics->getStepStats());
        $this->printTechStats($formatter, $timer, $memory);
    }

    /**
     * Prints scenarios list.
     *
     * @param OutputPrinter $printer
     * @param string        $intro
     * @param integer       $resultCode
     * @param array         $scenarioStats
     */
    private function printScenariosList(OutputPrinter $printer, $intro, $resultCode, array $scenarioStats)
    {
        if (!count($scenarioStats)) {
            return;
        }

        $style = $this->resultConverter->convertResultCodeToString($resultCode);
        $intro = $this->translator->trans($intro, array(), 'output');

        $printer->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $intro, $style));
        foreach ($scenarioStats as $stat) {
            $path = $this->relativizePaths((string)$stat);
            $printer->writeln(sprintf('    {+%s}%s{-%s}', $style, $path, $style));
        }

        $printer->writeln();
    }

    /**
     * Prints scenario and step counters.
     *
     * @param OutputPrinter $printer
     * @param string        $intro
     * @param array         $stats
     */
    public function printCounters(OutputPrinter $printer, $intro, array $stats)
    {
        $stats = array_filter($stats, function ($stats) { return 0 !== count($stats); });
        $totalCount = count(call_user_func_array('array_merge', array_values($stats)));

        $detailedStats = array();
        foreach ($stats as $resultCode => $codeStats) {
            $count = count($codeStats);
            $style = $this->resultConverter->convertResultCodeToString($resultCode);

            $transId = $style . '_count';
            $message = $this->translator->transChoice($transId, $count, array('%1%' => $count), 'output');

            $detailedStats[] = sprintf('{+%s}%s{-%s}', $style, $message, $style);
        }

        $message = $this->translator->transChoice($intro, $totalCount, array('%1%' => $totalCount), 'output');
        $printer->write($message);

        if (count($detailedStats)) {
            $printer->write(sprintf(' (%s)', implode(', ', $detailedStats)));
        }

        $printer->writeln();
    }

    /**
     * Prints tech stats (timer and memory counter).
     *
     * @param Formatter $formatter
     * @param Timer     $timer
     * @param Memory    $memory
     */
    private function printTechStats(Formatter $formatter, Timer $timer, Memory $memory)
    {
        $formatter->getOutputPrinter()->writeln(sprintf('%s (%s)', $timer, $memory));
    }

    /**
     * Transforms path to relative.
     *
     * @param string $path
     *
     * @return string
     */
    private function relativizePaths($path)
    {
        if (!$this->basePath) {
            return $path;
        }

        return str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $path);
    }
}
