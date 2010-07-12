<?php

namespace Everzet\Behat;

use \Everzet\Gherkin\Parser;
use \Everzet\Gherkin\Feature;
use \Everzet\Gherkin\Background;
use \Everzet\Gherkin\ScenarioOutline;
use \Everzet\Gherkin\Scenario;
use \Everzet\Gherkin\Step;
use \Everzet\Behat\Definitions\StepsContainer;
use \Everzet\Behat\Definitions\StepDefinition;
use \Everzet\Behat\Printers\Printer;
use \Everzet\Behat\Exceptions\Pending;
use \Everzet\Behat\Exceptions\Redundant;
use \Everzet\Behat\Exceptions\Ambiguous;
use \Everzet\Behat\Exceptions\Undefined;

class FeatureRuner
{
    protected $printer;
    protected $file;
    protected $steps;

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

    public function __construct($file, Printer $printer, StepsContainer $steps)
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException(sprintf('File %s does not exists', $file));
        }

        $this->file = $file;
        $this->printer = $printer;
        $this->steps = $steps;
    }

    public function run()
    {
        $parser = new Parser;
        $feature = $parser->parse(file_get_contents($this->file));
        $this->printer->logFeature($feature, $this->file);

        return $this->runFeature($feature);
    }

    public function runFeature(Feature $feature)
    {
        $statuses = $this->initStatusesArray();

        foreach ($feature->getBackgrounds() as $background) {
            $this->printer->logBackground($background);
            $scenarioStatuses = $this->runScenario($background);
            foreach ($scenarioStatuses as $status => $num) {
                $statuses[$status] += $num;
            }
        }
        foreach ($feature->getScenarios() as $scenario) {
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

    protected function logStep($code, Step $step, \Exception $e = null)
    {
        $this->printer->logStep(
            $step->getType(), $step->getText($values), null, null, $e
        );

        return $code;
    }

    protected function logStepDefinition($code, StepDefinition $definition, \Exception $e = null)
    {
        $this->printer->logStep(
            $code, $definition->getType(), $definition->getMatchedText(),
            $definition->getFile(), $definition->getLine(), $e
        );

        return $code;
    }

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
