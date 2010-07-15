<?php

namespace Everzet\Behat;

use \Everzet\Gherkin\I18n;
use \Everzet\Gherkin\Parser;
use \Everzet\Gherkin\Structures\Feature;
use \Everzet\Gherkin\Structures\Step;
use \Everzet\Gherkin\Structures\Scenario\Background;
use \Everzet\Gherkin\Structures\Scenario\ScenarioOutline;
use \Everzet\Gherkin\Structures\Scenario\Scenario;
use \Everzet\Behat\Stats\ScenarioStats;
use \Everzet\Behat\Stats\FeatureStats;
use \Everzet\Behat\Definitions\StepsContainer;
use \Everzet\Behat\Definitions\StepDefinition;
use \Everzet\Behat\Environment\World;
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

    /**
     * Initiates feature runer
     *
     * @param   string          $file       *.feature file path
     * @param   Printer         $printer    test output printer
     * @param   StepsContainer  $steps      steps container
     * 
     * @throws  \InvalidArgumentException   if feature file doesn't exists
     */
    public function __construct($file, Printer $printer, StepsContainer $steps,
                                World $world, I18n $i18n)
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException(sprintf('File %s does not exists', $file));
        }

        $this->file = $file;
        $this->printer = $printer;
        $this->steps = $steps;
        $this->world = $world;
        $this->i18n = $i18n;
    }

    /**
     * Abstract method to parse & test feature file
     *
     * @return  \Everzet\Behat\Stats\FeatureStats   feature test run statuses
     */
    public function run()
    {
        $parser = new Parser($this->i18n);
        $feature = $parser->parse(file_get_contents($this->file));

        return $this->runFeature($feature);
    }

    /**
     * Runs feature tests
     *
     * @param   Feature $feature                    feature instance
     * 
     * @return  \Everzet\Behat\Stats\FeatureStats   feature test run statuses
     */
    public function runFeature(Feature $feature)
    {
        $featureStats = new FeatureStats;

        $this->printer->logFeatureBegin($feature, $this->file);
        foreach ($feature->getScenarios() as $scenario) {
            if ($scenario instanceof ScenarioOutline) {
                $this->printer->logScenarioOutlineBegin($scenario);
                foreach ($scenario->getExamples()->getTable()->getHash() as $values) {
                    $this->world->flush();
                    $stats = $this->runFeatureBackgrounds($feature);

                    $this->printer->logScenarioBegin($scenario);
                    $stats->mergeStatuses($this->runScenario($scenario, $values));
                    $this->printer->logScenarioEnd($scenario);

                    $featureStats->addScenarioStatuses($stats);
                }
                $this->printer->logScenarioOutlineEnd($scenario);
            } else {
                $this->world->flush();
                $stats = $this->runFeatureBackgrounds($feature);

                $this->printer->logScenarioBegin($scenario);
                $stats->mergeStatuses($this->runScenario($scenario));
                $this->printer->logScenarioEnd($scenario);

                $featureStats->addScenarioStatuses($stats);
            }
        }
        $this->printer->logFeatureEnd($feature, $this->file);

        return $featureStats;
    }

    /**
     * Runs feature backgrounds (called between scenarios)
     *
     * @param   Feature $feature    feature instance
     * 
     * @return  \Everzet\Behat\Stats\ScenarioStats  background steps statuses
     */
    public function runFeatureBackgrounds(Feature $feature)
    {
        $backgroundsStats = new ScenarioStats;

        foreach ($feature->getBackgrounds() as $background) {
            $this->printer->logBackgroundBegin($background);
            $backgroundsStats->mergeStatuses($this->runScenario($background));
            $this->printer->logBackgroundEnd($background);
        }

        return $backgroundsStats;
    }

    /**
     * Runs Scenario tests
     *
     * @param   Background  $scenario               background instance
     * 
     * @return  \Everzet\Behat\Stats\ScenarioStats  scenario steps statuses
     */
    public function runScenario(Background $scenario, array $values = array())
    {
        $scenarioStats = new ScenarioStats;
        $skip = false;

        foreach ($scenario->getSteps() as $step) {
            $scenarioStats->addStepStatus($this->runStep($step, $values, $skip));
            if ('failed' === $scenarioStats->getLastStepStatus()) {
                $skip = true;
            }
        }

        return $scenarioStats;
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
    protected function logStep($code, Step $step, array $values = array(), \Exception $e = null)
    {
        $this->printer->logStep(
            $code, $step->getType(), $step->getText($values), null, null,
            $step->getArguments(), $e
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
    protected function logStepDefinition($code, StepDefinition $definition,
                                         array $args = array(), \Exception $e = null)
    {
        $this->printer->logStep(
            $code, $definition->getType(), $definition->getMatchedText(),
            $definition->getFile(), $definition->getLine(), $args, $e
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
     * @return  string          status code
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
                return $this->logStep('failed', $step, $values, $e);
            }
        } catch (Undefined $e) {
            return $this->logStep('undefined', $step, $values);
        }

        if ($skip) {
            return $this->logStepDefinition('skipped', $definition, $step->getArguments());
        } else {
            try {
                try {
                    $definition->run();
                    return $this->logStepDefinition('passed', $definition, $step->getArguments());
                } catch (Pending $e) {
                    return $this->logStepDefinition('pending', $definition, $step->getArguments());
                }
            } catch (\Exception $e) {
                return $this->logStepDefinition('failed', $definition, $step->getArguments(), $e);
            }
        }
    }
}
