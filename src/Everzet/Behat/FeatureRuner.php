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
use \Everzet\Behat\Printers\Printer;
use \Everzet\Behat\Exceptions\Redundant;
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
    protected $stepDefinitions;
    protected $worldClass;

    /**
     * Initiates feature runer
     *
     * @param   string          $file       *.feature file path
     * @param   Printer         $printer    test output printer
     * @param   StepsContainer  $steps      steps container
     * 
     * @throws  \InvalidArgumentException   if feature file doesn't exists
     */
    public function __construct($file, Printer $printer, \Iterator $stepDefinitions,
                                $worldClass, I18n $i18n)
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException(sprintf('File %s does not exists', $file));
        }

        $this->file = $file;
        $this->printer = $printer;
        $this->stepDefinitions = $stepDefinitions;
        $this->worldClass = $worldClass;
        $this->i18n = $i18n;
    }

    /**
     * Abstract method to parse & test feature file
     *
     * @return  \Everzet\Behat\Stats\FeatureStats   feature test run statuses
     */
    public function run()
    {
        $this->printer->setFile($this->file);
        $parser = new Parser($this->i18n);
        $feature = $parser->parse(file_get_contents($this->file));

        return $this->runFeature($feature);
    }

    /**
     * Creates & returns StepsContainer instance
     *
     * @return  StepsContainer
     */
    protected function createStepsContainer()
    {
        $class = $this->worldClass;
        $world = new $class;
        $steps = new StepsContainer($this->stepDefinitions, $world);

        return $steps;
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
                foreach ($this->runScenarioOutline($scenario, $feature) as $stats) {
                    $featureStats->addScenarioStatuses($stats);
                }
            } else {
                $steps = $this->createStepsContainer();
                $stats = $this->runFeatureBackgrounds($feature, $steps);
                $stats->mergeStatuses($this->runScenario($scenario, $steps));
                $featureStats->addScenarioStatuses($stats);
            }
        }
        $this->printer->logFeatureEnd($feature, $this->file);

        return $featureStats;
    }

    /**
     * Runs scenario outline tests
     * 
     * @param   ScenarioOutline     $outline        outline instance
     * @param   Feature             $feature        feature instance
     * 
     * @return  \Everzet\Behat\Stats\ScenarioStats  outline steps statuses
     */
    public function runScenarioOutline(ScenarioOutline $outline, Feature $feature)
    {
        $scenariosStats = array();

        $this->printer->logScenarioOutlineBegin($outline);
        foreach ($outline->getExamples()->getTable()->getHash() as $values) {
            $steps = $this->createStepsContainer();
            $stats = $this->runFeatureBackgrounds($feature, $steps);
            $stats->mergeStatuses($this->runScenarioSteps($outline, $values, true, $steps));

            $scenariosStats[] = $stats;
        }
        $this->printer->logScenarioOutlineEnd($outline);

        return $scenariosStats;
    }

    /**
     * Runs feature backgrounds (called between scenarios)
     *
     * @param   Feature         $feature    feature instance
     * @param   StepsContainer  $steps      steps definitions
     * 
     * @return  \Everzet\Behat\Stats\ScenarioStats  background steps statuses
     */
    public function runFeatureBackgrounds(Feature $feature, StepsContainer $steps)
    {
        $backgroundsStats = new ScenarioStats;

        foreach ($feature->getBackgrounds() as $background) {
            $this->printer->logBackgroundBegin($background);
            $backgroundsStats->mergeStatuses(
                $this->runScenarioSteps($background, array(), false, $steps)
            );
            $this->printer->logBackgroundEnd($background);
        }

        return $backgroundsStats;
    }

    /**
     * Runs Scenario tests
     *
     * @param   Scenario        $scenario   background instance
     * @param   StepsContainer  $steps      steps definitions
     * 
     * @return  \Everzet\Behat\Stats\ScenarioStats  scenario steps statuses
     */
    public function runScenario(Scenario $scenario, StepsContainer $steps)
    {
        $this->printer->logScenarioBegin($scenario);
        $scenarioStats = $this->runScenarioSteps($scenario, array(), false, $steps);
        $this->printer->logScenarioEnd($scenario);

        return $scenarioStats;
    }

    /**
     * Runs Scenario steps
     *
     * @param   Background      $scenario   background instance
     * @param   array           $values     examples values
     * @param   boolean         $inOutline  are we in outline?
     * @param   StepsContainer  $steps      steps definitions
     * 
     * @return  \Everzet\Behat\Stats\ScenarioStats  scenario steps statuses
     */
    public function runScenarioSteps(Background $scenario, array $values = array(),
                                     $inOutline = false, StepsContainer $steps)
    {
        $scenarioStats = new ScenarioStats;
        $skip = false;

        foreach ($scenario->getSteps() as $step) {
            $scenarioStats->addStepStatus($this->runStep($step, $values, $skip, $steps));
            if ('failed' === $scenarioStats->getLastStepStatus()) {
                $skip = true;
            }
        }
        if ($inOutline) {
            $this->printer->logIntermediateOutlineScenario($scenario);
        }

        return $scenarioStats;
    }

    /**
     * Runs Step test
     *
     * @param   Step            $step       step instance to test
     * @param   array           $values     example tokens
     * @param   boolean         $skip       do we need to mark this step as skipped
     * @param   StepsContainer  $steps      steps definitions
     * 
     * @return  string                      status code
     * 
     * @throws  \Everzet\Behat\Exceptions\Pending       if step throws Pending exception
     * @throws  \Everzet\Behat\Exceptions\Ambiguous     if step matches multiple definitions
     * @throws  \Everzet\Behat\Exceptions\Undefined     if step definition not found
     */
    public function runStep(Step $step, array $values = array(), $skip = false,
                            StepsContainer $steps)
    {
        try {
            try {
                $definition = $steps->findDefinition($step, $values);
            } catch (Ambiguous $e) {
                return $this->logStep('failed', $step, $values, $e);
            }
        } catch (Undefined $e) {
            return $this->logStep('undefined', $step, $values);
        }

        if ($skip) {
            return $this->logStepDefinition(
                'skipped', $step->getType(), $definition, $step->getArguments()
            );
        } else {
            try {
                try {
                    $definition->run();
                    return $this->logStepDefinition(
                        'passed', $step->getType(), $definition, $step->getArguments()
                    );
                } catch (Pending $e) {
                    return $this->logStepDefinition(
                        'pending', $step->getType(), $definition, $step->getArguments()
                    );
                }
            } catch (\Exception $e) {
                return $this->logStepDefinition(
                    'failed', $step->getType(), $definition, $step->getArguments(), $e
                );
            }
        }
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
    protected function logStepDefinition($code, $type, StepDefinition $definition,
                                         array $args = array(), \Exception $e = null)
    {
        $this->printer->logStep(
            $code, $type, $definition->getMatchedText(),
            $definition->getFile(), $definition->getLine(), $args, $e
        );

        return $code;
    }
}
