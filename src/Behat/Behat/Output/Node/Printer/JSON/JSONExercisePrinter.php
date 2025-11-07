<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JSON;

use Behat\Behat\Output\Node\EventListener\JSON\JSONDurationListener;
use Behat\Behat\Output\Node\Printer\ExercisePrinter;
use Behat\Behat\Output\Statistics\PhaseStatistics;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSONOutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

final class JSONExercisePrinter implements ExercisePrinter
{
    public function __construct(
        private readonly PhaseStatistics $statistics,
        private readonly JSONDurationListener $durationListener,
    ) {
    }

    public function printHeader(Formatter $formatter): void
    {
        $this->statistics->reset();

        $outputPrinter = $formatter->getOutputPrinter();
        assert($outputPrinter instanceof JSONOutputPrinter);
        $outputPrinter->createNewFile();
    }

    public function printFooter(Formatter $formatter): void
    {
        $stats = $this->statistics->getScenarioStatCounts();

        $totalCount = 0 === count($stats) ? 0 : (int) array_sum($stats);

        $outputPrinter = $formatter->getOutputPrinter();
        assert($outputPrinter instanceof JSONOutputPrinter);

        $exerciseAttributes = [
            'tests' => $totalCount,
            'skipped' => $stats[TestResult::SKIPPED],
            'failed' => $stats[TestResult::FAILED],
            'pending' => $stats[TestResult::PENDING],
            'undefined' => $stats[TestResult::UNDEFINED],
        ];

        if ($formatter->getParameter('timer')) {
            $exerciseAttributes['time'] = (float) $this->durationListener->getExerciseDuration();
        }

        $outputPrinter->extendExerciseAttributes($exerciseAttributes);

        $formatter->getOutputPrinter()->flush();
    }
}
