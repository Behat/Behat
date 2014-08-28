<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JUnit;

use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\ScenarioElementPrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Behat\Output\Statistics\Statistics;

/**
 * Prints the <testcase> element.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class JUnitScenarioPrinter implements ScenarioElementPrinter
{
    /**
     * @var ResultToStringConverter
     */
    private $resultConverter;

    public function __construct(ResultToStringConverter $resultConverter)
    {
        $this->resultConverter = $resultConverter;
    }

    /**
     * {@inheritDoc}
     */
    public function printOpenTag(Formatter $formatter, FeatureNode $feature, Scenario $scenario, TestResult $result)
    {
        $name = implode(' ', array_map(function ($l) {
            return trim($l);
        }, explode("\n", $scenario->getTitle())));

        $formatter->getOutputPrinter()->addTestcase(array(
            'name' => $name,
            'status' => $this->resultConverter->convertResultToString($result)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function printCloseTag(Formatter $formatter)
    {
    }
}
