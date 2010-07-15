<?php

namespace Everzet\Behat\Printers;

use \Everzet\Gherkin\Structures\Feature;
use \Everzet\Gherkin\Structures\Scenario\Background;
use \Everzet\Gherkin\Structures\Scenario\ScenarioOutline;
use \Everzet\Gherkin\Structures\Scenario\Scenario;
use \Everzet\Behat\Stats\TestStats;
use \Everzet\Behat\Definitions\StepsContainer;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Printer.
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Printer
{
    /**
     * Prints feature begin
     *
     * @param   Feature $feature    feature instance
     * @param   string  $file       feature file
     */
    public function logFeatureBegin(Feature $feature, $file);

    /**
     * Prints feature end
     *
     * @param   Feature $feature    feature instance
     * @param   string  $file       feature file
     */
    public function logFeatureEnd(Feature $feature, $file);

    /**
     * Prints background begin
     *
     * @param   Background  $background background instance
     */
    public function logBackgroundBegin(Background $background);

    /**
     * Prints background end
     *
     * @param   Background  $background background instance
     */
    public function logBackgroundEnd(Background $background);

    /**
     * Prints scenario outline begin
     *
     * @param   ScenarioOutline $scenario   scenario outline instance
     */
    public function logScenarioOutlineBegin(ScenarioOutline $scenario);

    /**
     * Prints scenario outline end
     *
     * @param   ScenarioOutline $scenario   scenario outline instance
     */
    public function logScenarioOutlineEnd(ScenarioOutline $scenario);

    /**
     * Prints scenario begin
     *
     * @param   Scenario    $scenario   scenario instance
     */
    public function logScenarioBegin(Scenario $scenario);

    /**
     * Prints scenario end
     *
     * @param   Scenario    $scenario   scenario instance
     */
    public function logScenarioEnd(Scenario $scenario);

    /**
     * Prints step
     *
     * @param   string      $code   status code
     * @param   string      $type   step type
     * @param   string      $text   step text (description)
     * @param   string      $file   step definition file
     * @param   integer     $line   step definition line
     * @param   array       $args   step arguments
     * @param   \Exception  $e      step exception to print
     */
     public function logStep($code, $type, $text, $file = null,
                             $line = null, array $args = array(), \Exception $e = null);

    /**
     * Prints step arguments
     *
     * @param   string  $code   status code
     * @param   array   $args   arguments list
     */
    public function logStepArguments($code, array $args);

    /**
     * Prints tests statistics
     *
     * @param   TestStats       $stats  runer statistics
     * @param   StepsContainer  $steps  steps definition container
     */
    public function logStats(TestStats $stats, StepsContainer $steps);
}
