<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints scenario open and close tags.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
interface ScenarioElementPrinter
{
    /**
     * Prints scenario open tag using provided printer.
     *
     * @param Formatter   $formatter
     * @param FeatureNode $feature
     * @param Scenario    $scenario
     * @param TestResult  $result
     */
    public function printOpenTag(Formatter $formatter, FeatureNode $feature, Scenario $scenario, TestResult $result);

    /**
     * Prints scenario close tag using provided printer.
     *
     * @param Formatter  $formatter
     */
    public function printCloseTag(Formatter $formatter);
}
