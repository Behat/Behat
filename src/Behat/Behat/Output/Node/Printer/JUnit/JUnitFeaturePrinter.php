<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JUnit;

use Behat\Behat\Output\Node\EventListener\JUnit\JUnitDurationListener;
use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Behat\Output\Statistics\PhaseStatistics;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JUnitOutputPrinter;
use Behat\Testwork\PathOptions\Printer\ConfigurablePathPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints the <testsuite> element.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class JUnitFeaturePrinter implements FeaturePrinter
{
    private ?FeatureNode $currentFeature = null;

    public function __construct(
        private readonly PhaseStatistics $statistics,
        private readonly ?JUnitDurationListener $durationListener = null,
        private readonly ?ConfigurablePathPrinter $configurablePathPrinter = null,
    ) {
    }

    public function printHeader(Formatter $formatter, FeatureNode $feature): void
    {
        $this->statistics->reset();
        $this->currentFeature = $feature;
        /** @var JUnitOutputPrinter $outputPrinter */
        $outputPrinter = $formatter->getOutputPrinter();
        $outputPrinter->addTestsuite();
    }

    public function printFooter(Formatter $formatter, TestResult $result): void
    {
        $stats = $this->statistics->getScenarioStatCounts();

        $totalCount = 0 === count($stats) ? 0 : (int) array_sum($stats);

        /** @var JUnitOutputPrinter $outputPrinter */
        $outputPrinter = $formatter->getOutputPrinter();

        $featureAttributes = [
            'name' => $this->currentFeature->getTitle(),
        ];

        $file = $this->currentFeature->getFile();
        if ($file && $this->configurablePathPrinter instanceof ConfigurablePathPrinter) {
            $featureAttributes['file'] = $this->configurablePathPrinter->processPathsInText(
                $file,
                applyEditorUrl: false,
            );
        }

        $featureAttributes += [
            'tests' => $totalCount,
            'skipped' => $stats[TestResult::SKIPPED],
            'failures' => $stats[TestResult::FAILED],
            'errors' => $stats[TestResult::PENDING] + $stats[TestResult::UNDEFINED],
        ];

        if ($formatter->getParameter('timer') && $this->durationListener instanceof JUnitDurationListener) {
            $featureAttributes['time'] = $this->durationListener->getFeatureDuration($this->currentFeature);
        }

        $outputPrinter->extendTestsuiteAttributes($featureAttributes);

        $this->statistics->reset();
        $this->currentFeature = null;
    }
}
