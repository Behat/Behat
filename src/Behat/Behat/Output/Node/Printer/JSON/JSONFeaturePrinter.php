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
use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Behat\Output\Statistics\PhaseStatistics;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSONOutputPrinter;
use Behat\Testwork\PathOptions\Printer\ConfigurablePathPrinter;
use Behat\Testwork\Tester\Result\TestResult;

final class JSONFeaturePrinter implements FeaturePrinter
{
    private FeatureNode $currentFeature;

    public function __construct(
        private readonly PhaseStatistics $statistics,
        private readonly JSONDurationListener $durationListener,
        private readonly ConfigurablePathPrinter $configurablePathPrinter,
    ) {
    }

    public function printHeader(Formatter $formatter, FeatureNode $feature): void
    {
        $this->statistics->reset();
        $this->currentFeature = $feature;
        $outputPrinter = $formatter->getOutputPrinter();
        assert($outputPrinter instanceof JSONOutputPrinter);
        $outputPrinter->addFeature();
    }

    public function printFooter(Formatter $formatter, TestResult $result): void
    {
        $stats = $this->statistics->getScenarioStatCounts();

        $totalCount = 0 === count($stats) ? 0 : (int) array_sum($stats);

        $outputPrinter = $formatter->getOutputPrinter();
        assert($outputPrinter instanceof JSONOutputPrinter);

        $featureAttributes = [
            'name' => $this->currentFeature->getTitle() ?? '',
        ];

        $file = $this->currentFeature->getFile();
        if ($file) {
            $featureAttributes['file'] = $this->configurablePathPrinter->processPathsInText(
                $file,
                applyEditorUrl: false,
            );
        }

        $featureAttributes += [
            'tests' => $totalCount,
            'skipped' => $stats[TestResult::SKIPPED],
            'failed' => $stats[TestResult::FAILED],
            'pending' => $stats[TestResult::PENDING],
            'undefined' => $stats[TestResult::UNDEFINED],
        ];

        if ($formatter->getParameter('timer')) {
            $featureAttributes['time'] = (float) $this->durationListener->getFeatureDuration($this->currentFeature);
        }

        $outputPrinter->extendFeatureAttributes($featureAttributes);
    }
}
