<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Behat\Tester\Result\StepTestResult;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Formatter;

/**
 * Behat step printer.
 *
 * Prints step with optional results.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface StepPrinter
{
    /**
     * Prints step using provided printer.
     *
     * @param Formatter      $formatter
     * @param Scenario       $scenario
     * @param StepNode       $step
     * @param StepTestResult $result
     */
    public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepTestResult $result = null);
}
