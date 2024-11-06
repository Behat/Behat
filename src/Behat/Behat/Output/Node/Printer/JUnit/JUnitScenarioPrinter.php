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
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JUnitOutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints the <testcase> element.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class JUnitScenarioPrinter
{
    /**
     * @var ResultToStringConverter
     */
    private $resultConverter;

    /**
     * @var JUnitDurationListener|null
     */
    private $durationListener;

    public function __construct(ResultToStringConverter $resultConverter, ?JUnitDurationListener $durationListener = null)
    {
        $this->resultConverter = $resultConverter;
        $this->durationListener = $durationListener;
    }

    /**
     * {@inheritDoc}
     */
    public function printOpenTag(Formatter $formatter, FeatureNode $feature, ScenarioLikeInterface $scenario, TestResult $result, ?string $file = null): void
    {
        $name = $this->convertMultipleLinesToOne(
            $scenario instanceof ExampleNode
                ? $this->buildExampleName($scenario)
                : $scenario->getTitle()
        );

        /** @var JUnitOutputPrinter $outputPrinter */
        $outputPrinter = $formatter->getOutputPrinter();

        $testCaseAttributes = array(
            'name'      => $name,
            'classname' => $feature->getTitle(),
            'status'    => $this->resultConverter->convertResultToString($result),
            'time'      => $this->durationListener ? $this->durationListener->getDuration($scenario) : ''
        );

        if ($file) {
            $cwd = realpath(getcwd());
            $testCaseAttributes['file'] = substr($file, 0, strlen($cwd)) === $cwd
                ? ltrim(substr($file, strlen($cwd)), DIRECTORY_SEPARATOR)
                : $file;
        }

        $outputPrinter->addTestcase($testCaseAttributes);
    }

    private function buildExampleName(ExampleNode $scenario): string
    {
        return sprintf(
            '%s (%s)',
            $scenario->getOutlineTitle(),
            implode(
                ', ',
                array_map(
                    static function ($key, $val) {
                        return "$key: $val";
                    },
                    array_keys($scenario->getTokens()),
                    array_values($scenario->getTokens())
                )
            )
        );
    }

    private function convertMultipleLinesToOne(string $string): string
    {
        return implode(
            ' ',
            array_map(
                static function ($line) {
                    return trim($line);
                },
                explode("\n", $string)
            )
        );
    }
}
