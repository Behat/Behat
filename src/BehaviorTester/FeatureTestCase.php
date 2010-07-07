<?php

namespace BehaviorTester;

abstract class FeatureTestCase extends \PHPUnit_Framework_TestCase
{
    protected $feature;
    protected $steps = array();

    abstract protected function getStepsPath();

    protected function initStepDefinition()
    {
        $iterator = new \RecursiveDirectoryIterator(
            $this->getStepsPath(),
            \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
        );
        $iterator = new \RecursiveIteratorIterator(
            $iterator, \RecursiveIteratorIterator::SELF_FIRST
        );

        $this->steps = array();
        $t = $this;
        foreach ($iterator as $file) {
            require $file;
        }
    }

    public function defineStep($type, $definition, $callback)
    {
        $this->steps[$definition] = array('type' => $type, 'callback' => $callback);

        return $this;
    }

    public function __call($command, $arguments)
    {
        return $this->defineStep($command, $arguments[0], $arguments[1]);
    }

    public function getFeature()
    {
        if (null === $this->feature) {
            $parser = new \Gherkin\Parser;
            $this->feature = $parser->parse(file_get_contents($this->getFeaturePath()));
        }

        return $this->feature;
    }

    abstract protected function getFeaturesPath();
    abstract protected function getFeatureName();

    protected function getFeaturePath()
    {
        return $this->getFeaturesPath() . $this->getFeatureName();
    }

    protected function setUp()
    {
        $this->initStepDefinition();
        foreach ($this->getFeature()->getBackgrounds() as $background) {
            $this->runScenario($background);
        }
    }

    public function scenariosProvider()
    {
        $scenarios = array();

        array_walk($this->getFeature()->getScenarios(), function($scenario) use(&$scenarios) {
            $scenarios[] = array($scenario);
        });

        return $scenarios;
    }

    /**
     * @dataProvider scenariosProvider
     */
    public function testScenario(\Gherkin\Scenario $scenario)
    {
        if ($scenario instanceof \Gherkin\ScenarioOutline) {
            foreach ($scenario->getExamples() as $parameters) {
                $this->runScenario($scenario, $parameters);
            }
        } else {
            $this->runScenario($scenario);
        }
    }

    protected function runScenario(\Gherkin\Background $scenario, array $parameters = array())
    {
        foreach ($scenario->getSteps() as $step) {
            $this->runStep($step, $parameters);
        }
    }

    protected function runStep(\Gherkin\Step $step, array $parameters = array())
    {
        $description = $step->getText($parameters);

        foreach ($this->steps as $regex => $params) {
            if (preg_match($regex, $description, $values)) {
                call_user_func_array(
                    $params['callback'], array_merge(array_slice($values, 1), $step->getArguments())
                );

                return;
            }
        }

        $this->notImplemented($description);
    }

    protected function notImplemented($action)
    {
        if (strstr($action, ' ')) {
            $this->markTestIncomplete("Step /$action/ not implemented.");
        }

        throw new BadMethodCallException("Method $action not defined.");
    }
}
