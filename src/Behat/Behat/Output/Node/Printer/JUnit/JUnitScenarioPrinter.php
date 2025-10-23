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
use Behat\Behat\Output\Node\EventListener\JUnit\JUnitOutlineStoreListener;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JUnitOutputPrinter;
use Behat\Testwork\PathOptions\Printer\ConfigurablePathPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints the <testcase> element.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class JUnitScenarioPrinter
{
    /**
     * @var OutlineNode
     */
    private $lastOutline;

    /**
     * @var int
     */
    private $outlineStepCount;

    public function __construct(
        private readonly ResultToStringConverter $resultConverter,
        private readonly JUnitOutlineStoreListener $outlineStoreListener,
        private readonly ?JUnitDurationListener $durationListener = null,
        private readonly ?ConfigurablePathPrinter $configurablePathPrinter = null,
    ) {
    }

    public function printOpenTag(Formatter $formatter, FeatureNode $feature, ScenarioLikeInterface $scenario, TestResult $result, ?string $file = null)
    {
        $name = implode(' ', array_map(fn ($l) => trim($l), explode("\n", $scenario->getTitle() ?? '')));

        if ($scenario instanceof ExampleNode) {
            $name = $this->buildExampleName($scenario);
        }

        /** @var JUnitOutputPrinter $outputPrinter */
        $outputPrinter = $formatter->getOutputPrinter();

        $testCaseAttributes = [
            'name' => $name,
            'classname' => $feature->getTitle(),
            'status' => $this->resultConverter->convertResultToString($result),
            'time' => $this->durationListener instanceof JUnitDurationListener ? $this->durationListener->getDuration($scenario) : '',
        ];

        if ($file && $this->configurablePathPrinter instanceof ConfigurablePathPrinter) {
            $testCaseAttributes['file'] = $this->configurablePathPrinter->processPathsInText(
                $file,
                applyEditorUrl: false,
            );
        }

        $outputPrinter->addTestcase($testCaseAttributes);
    }

    /**
     * @return string
     */
    private function buildExampleName(ExampleNode $scenario)
    {
        $currentOutline = $this->outlineStoreListener->getCurrentOutline($scenario);
        if ($currentOutline === $this->lastOutline) {
            ++$this->outlineStepCount;
        } else {
            $this->lastOutline = $currentOutline;
            $this->outlineStepCount = 1;
        }

        $name = $currentOutline->getTitle() . ' #' . $this->outlineStepCount;

        return $name;
    }
}
