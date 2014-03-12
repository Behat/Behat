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
 * Prints scenario headers and footers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ScenarioPrinter
{
    /**
     * Prints scenario header using provided printer.
     *
     * @param Formatter   $formatter
     * @param FeatureNode $feature
     * @param Scenario    $scenario
     */
    public function printHeader(Formatter $formatter, FeatureNode $feature, Scenario $scenario);

    /**
     * Prints scenario footer using provided printer.
     *
     * @param Formatter  $formatter
     * @param TestResult $result
     */
    public function printFooter(Formatter $formatter, TestResult $result);
}
