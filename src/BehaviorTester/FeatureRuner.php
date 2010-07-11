<?php

namespace BehaviorTester;

use \Gherkin\Parser;
use \Gherkin\Feature;
use \Gherkin\Background;
use \Gherkin\ScenarioOutline;
use \Gherkin\Scenario;
use \Gherkin\Step;

use \BehaviorTester\StepsDefinition;
use \BehaviorTester\OutputLogger;

use \BehaviorTester\Exceptions\Pending;
use \BehaviorTester\Exceptions\Redundant;
use \BehaviorTester\Exceptions\Ambiguous;
use \BehaviorTester\Exceptions\Undefined;

use \Symfony\Components\Console\Output\OutputInterface;

class FeatureRuner
{
    protected $output;
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

    public function __construct($file, OutputLogger $output, StepsDefinition $steps)
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException(sprintf('File %s does not exists', $file));
        }

        $this->file = $file;
        $this->output = $output;
        $this->steps = $steps;
    }

    public function run()
    {
        $parser = new Parser;
        $feature = $parser->parse(file_get_contents($this->file));
        $this->output->logFeature($feature, $this->file);

        return $this->runFeature($feature);
    }

    public function runFeature(Feature $feature)
    {
        $statuses = $this->initStatusesArray();

        foreach ($feature->getBackgrounds() as $background) {
            $this->output->logBackground($background);
            $scenarioStatuses = $this->runScenario($background);
            foreach ($scenarioStatuses as $status => $num) {
                $statuses[$status] += $num;
            }
        }
        foreach ($feature->getScenarios() as $scenario) {
            if ($scenario instanceof ScenarioOutline) {
                $this->output->logScenarioOutline($scenario);
                $scenarioStatuses = $this->runScenarioOutline($scenario);
            } else {
                $this->output->logScenario($scenario);
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

    public function runStep(Step $step, array $values = array(), $skip = false)
    {
        try {
            try {
                $definition = $this->steps->findDefinition($step, $values);
            } catch (Ambiguous $e) {
                $this->output->logStep(
                    'failed', $step->getType(), $step->getText($values), null, null, $e
                );
                return 'failed';
            }
        } catch (Undefined $e) {
            $this->output->logStep('undefined', $step->getType(), $step->getText($values));
            return 'undefined';
        }

        if ($skip) {
            $this->output->logStep(
                'skipped', $definition['type'], $definition['description'],
                $definition['file'], $definition['line']
            );
            return 'skipped';
        } else {
            try {
                try {
                    call_user_func_array($definition['callback'], $definition['values']);
                    $this->output->logStep(
                        'passed', $definition['type'], $definition['description'],
                        $definition['file'], $definition['line']
                    );
                    return 'passed';
                } catch (Pending $e) {
                    $this->output->logStep(
                        'pending', $definition['type'], $definition['description'],
                        $definition['file'], $definition['line']
                    );
                    return 'pending';
                }
            } catch (\Exception $e) {
                $this->output->logStep(
                    'failed', $definition['type'], $definition['description'],
                    $definition['file'], $definition['line'], $e
                );
                return 'failed';
            }
        }
    }
}
