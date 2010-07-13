<?php

namespace Everzet\Behat;

use \Everzet\Gherkin\Parser;
use \Everzet\Gherkin\Feature;
use \Everzet\Gherkin\Background;
use \Everzet\Gherkin\ScenarioOutline;
use \Everzet\Gherkin\Scenario;
use \Everzet\Gherkin\Step;
use \Everzet\Behat\World;
use \Everzet\Behat\Definitions\StepsContainer;
use \Everzet\Behat\Definitions\StepDefinition;
use \Everzet\Behat\Printers\Printer;
use \Everzet\Behat\Exceptions\Pending;
use \Everzet\Behat\Exceptions\Ambiguous;
use \Everzet\Behat\Exceptions\Undefined;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Runs specific feature
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureRuner
{
    protected $printer;
    protected $file;
    protected $steps;
    protected $world;

    protected function initStatusesArray()
    {
        return array(
            'failed'    => 0,
            'passed'    => 0,
            'skipped'   => 0,
            'undefined' => 0,
            'pending'   => 0
        );
    }

    /**
     * Initiates feature runer
     *
     * @param   string          $file       *.feature file path
     * @param   Printer         $printer    test output printer
     * @param   StepsContainer  $steps      steps container
     * 
     * @throws  \InvalidArgumentException   if feature file doesn't exists
     */
    public function __construct($file, Printer $printer, StepsContainer $steps, World $world)
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException(sprintf('File %s does not exists', $file));
        }

        $this->world = $world;
        $this->file = $file;
        $this->printer = $printer;
        $this->steps = $steps;
    }

    /**
     * Abstract method to parse & test feature file
     *
     * @return  mixed   feature test statuses
     */
    public function run()
    {
        $parser = new Parser;
        $feature = $parser->parse(file_get_contents($this->file));
        $this->printer->logFeature($feature, $this->file);

        return $this->runFeature($feature);
    }

    /**
     * Runs feature tests
     *
     * @param   Feature $feature    feature instance
     * 
     * @return  mixed               status codes
     */
    public function runFeature(Feature $feature)
    {
        $statuses = $this->initStatusesArray();

        foreach ($feature->getScenarios() as $scenario) {
            $this->world->flush();
            foreach ($feature->getBackgrounds() as $background) {
                $this->printer->logBackground($background);
                $scenarioStatuses = $this->runScenario($background);
                foreach ($scenarioStatuses as $status => $num) {
                    $statuses[$status] += $num;
                }
            }
            if ($scenario instanceof ScenarioOutline) {
                $this->printer->logScenarioOutline($scenario);
                $scenarioStatuses = $this->runScenarioOutline($scenario);
            } else {
                $this->printer->logScenario($scenario);
                $scenarioStatuses = $this->runScenario($scenario);
            }
            foreach ($scenarioStatuses as $status => $num) {
                $statuses[$status] += $num;
            }
        }

        return $statuses;
    }

    /**
     * Runs Scenario Outline tests
     *
     * @param   ScenarioOutline $scenario   ScenarioOutline instance
     * 
     * @return  mixed                       status codes
     */
    public function runScenarioOutline(ScenarioOutline $scenario)
    {
        $statuses = $this->initStatusesArray();

        foreach ($scenario->getExamples() as $values) {
            foreach ($this->runScenario($scenario, $values) as $status => $num) {
                $statuses[$status] += $num;
            }
        }

        return $statuses;
    }

    /**
     * Runs Scenario tests
     *
     * @param   Background  $scenario   background instance
     * 
     * @return  mixed                   status codes
     */
    public function runScenario(Background $scenario, array $values = array())
    {
        $statuses = $this->initStatusesArray();
        $skip = false;

        foreach ($scenario->getSteps() as $step) {
            $status = $this->runStep($step, $values, $skip);
            if ('failed' === $status) {
                $skip = true;
            }
            $statuses[$status]++;
        }

        return $statuses;
    }

    /**
     * Calls step printer with specific step
     *
     * @param   string      $code   step status code
     * @param   Step        $step   step instance
     * @param   Exception   $e      throwed exception
     * 
     * @return  string              step status code
     */
    protected function logStep($code, Step $step, \Exception $e = null)
    {
        $this->printer->logStep(
            $step->getType(), $step->getText($values), null, null, $e
        );

        return $code;
    }

    /**
     * Calls step printer with specific step definition
     *
     * @param   string          $code           step status code
     * @param   StepDefinition  $definition     step definition instance
     * @param   Exception       $e              throwed exception
     * 
     * @return  string                          step status code
     */
    protected function logStepDefinition($code, StepDefinition $definition, \Exception $e = null)
    {
        $this->printer->logStep(
            $code, $definition->getType(), $definition->getMatchedText(),
            $definition->getFile(), $definition->getLine(), $e
        );

        return $code;
    }

    /**
     * Runs Step test
     *
     * @param   Step    $step   step instance to test
     * @param   array   $values example tokens
     * @param   boolean $skip   do we need to mark this step as skipped
     * 
     * @return  mixed           status codes
     * 
     * @throws  \Everzet\Behat\Exceptions\Pending       if step throws Pending exception
     * @throws  \Everzet\Behat\Exceptions\Ambiguous     if step matches multiple definitions
     * @throws  \Everzet\Behat\Exceptions\Undefined     if step definition not found
     */
    public function runStep(Step $step, array $values = array(), $skip = false)
    {
        try {
            try {
                $definition = $this->steps->findDefinition($step, $values);
            } catch (Ambiguous $e) {
                return $this->logStep('failed', $step, $e);
            }
        } catch (Undefined $e) {
            return $this->logStep('undefined', $step);
        }

        if ($skip) {
            return $this->logStepDefinition('skipped', $definition);
        } else {
            try {
                try {
                    $definition->run();
                    return $this->logStepDefinition('passed', $definition);
                } catch (Pending $e) {
                    return $this->logStepDefinition('pending', $definition);
                }
            } catch (\Exception $e) {
                return $this->logStepDefinition('failed', $definition, $e);
            }
        }
    }
}
