<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Gherkin\Element\FeatureElement;
use Everzet\Gherkin\Element\Scenario\ScenarioElement;
use Everzet\Gherkin\Element\Scenario\ScenarioOutlineElement;

use Everzet\Behat\Exception\BehaviorException;

class FeatureRunner extends BaseRunner implements RunnerInterface
{
    protected $feature;

    public function __construct(FeatureElement $feature, Container $container,
                                RunnerInterface $parent)
    {
        $this->feature = $feature;

        foreach ($feature->getScenarios() as $scenario) {
            if ($scenario instanceof ScenarioOutlineElement) {
                $this->addChildRunner(new ScenarioOutlineRunner(
                    $scenario
                  , $feature->getBackground()
                  , $container
                  , $this
                ));
            } elseif ($scenario instanceof ScenarioElement) {
                $this->addChildRunner(new ScenarioRunner(
                    $scenario
                  , $feature->getBackground()
                  , $container
                  , $this
                ));
            } else {
                throw new BehaviorException('Unknown scenario type: ' . get_class($scenario));
            }
        }

        parent::__construct('feature', $container->getEventDispatcherService(), $parent);
    }

    public function getFeature()
    {
        return $this->feature;
    }

    protected function doRun()
    {
        foreach ($this as $runner) {
            $runner->run();
        }
    }
}
