<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Element\FeatureElement;
use Everzet\Gherkin\Element\Scenario\ScenarioElement;
use Everzet\Gherkin\Element\Scenario\ScenarioOutlineElement;

use Everzet\Behat\Exception\BehaviorException;

class FeatureRunner extends BaseRunner implements RunnerInterface
{
    protected $feature;
    protected $dispatcher;
    protected $scenarioRunners = array();

    public function __construct(FeatureElement $feature, Container $container)
    {
        $this->feature      = $feature;
        $this->dispatcher   = $container->getEventDispatcherService();

        foreach ($feature->getScenarios() as $scenario) {
            if ($scenario instanceof ScenarioOutlineElement) {
                $this->scenarioRunners[] = new ScenarioOutlineRunner(
                    $scenario
                  , $feature->getBackground()
                  , $container
                );
            } elseif ($scenario instanceof ScenarioElement) {
                $this->scenarioRunners[] = new ScenarioRunner(
                    $scenario
                  , $feature->getBackground()
                  , $container
                );
            } else {
                throw new BehaviorException('Unknown scenario type: ' . get_class($scenario));
            }
        }
    }

    public function getFeature()
    {
        return $this->feature;
    }

    public function getStatus()
    {
        return $this->getStatusFromArray($this->scenarioRunners);
    }

    public function run(RunnerInterface $caller = null)
    {
        $this->setCaller($caller);
        $this->dispatcher->notify(new Event($this, 'feature.pre_test'));

        foreach ($this->scenarioRunners as $runner) {
            $runner->run($this);
        }

        $this->dispatcher->notify(new Event($this, 'feature.post_test'));
    }
}
