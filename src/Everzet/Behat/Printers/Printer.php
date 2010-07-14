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
     * Prints feature
     *
     * @param   Feature $feature    feature instance
     * @param   string  $file       feature file
     */
    public function logFeature(Feature $feature, $file);

    /**
     * Prints background
     *
     * @param   Background  $background background instance
     */
    public function logBackground(Background $background);

    /**
     * Prints scenario outline
     *
     * @param   ScenarioOutline $scenario   scenario outline instance
     */
    public function logScenarioOutline(ScenarioOutline $scenario);

    /**
     * Prints scenario
     *
     * @param   Scenario    $scenario   scenario instance
     */
    public function logScenario(Scenario $scenario);

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
