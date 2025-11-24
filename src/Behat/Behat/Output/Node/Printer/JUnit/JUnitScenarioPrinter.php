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
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\NamedScenarioInterface;
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
    public function __construct(
        private readonly ResultToStringConverter $resultConverter,
        private readonly ?JUnitDurationListener $durationListener = null,
        private readonly ?ConfigurablePathPrinter $configurablePathPrinter = null,
    ) {
    }

    public function printOpenTag(Formatter $formatter, FeatureNode $feature, ScenarioLikeInterface $scenario, TestResult $result, ?string $file = null)
    {
        assert($scenario instanceof NamedScenarioInterface);
        $name = implode(' ', array_map(fn ($l) => trim($l), explode("\n", $scenario->getName() ?? '')));

        /** @var JUnitOutputPrinter $outputPrinter */
        $outputPrinter = $formatter->getOutputPrinter();

        $testCaseAttributes = [
            'name' => $name,
            'classname' => $feature->getTitle(),
            'status' => $this->resultConverter->convertResultToString($result),
        ];

        if ($formatter->getParameter('timer') && $this->durationListener instanceof JUnitDurationListener) {
            $testCaseAttributes['time'] = $this->durationListener->getDuration($scenario);
        }

        if ($file && $this->configurablePathPrinter instanceof ConfigurablePathPrinter) {
            $testCaseAttributes['file'] = $this->configurablePathPrinter->processPathsInText(
                $file,
                applyEditorUrl: false,
            );
        }

        $outputPrinter->addTestcase($testCaseAttributes);
    }
}
