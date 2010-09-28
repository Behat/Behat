<?php

namespace Everzet\Behat\Tester;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\NodeVisitorInterface;
use Everzet\Gherkin\Node\ScenarioNode;
use Everzet\Gherkin\Node\OutlineNode;

use Everzet\Behat\Exception\BehaviorException;

class FeatureTester implements NodeVisitorInterface
{
    protected $container;
    protected $dispatcher;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->getEventDispatcherService();
    }

    public function visit($feature)
    {
        $this->dispatcher->notify(new Event($feature, 'feature.run.before'));

        $result = 0;

        // Filter scenarios
        $event = new Event($this, 'feature.run.filter_scenarios');
        $this->dispatcher->filter($event, $feature->getScenarios());

        // Test filtered scenarios
        foreach ($event->getReturnValue() as $scenario) {
            if ($scenario instanceof OutlineNode) {
                $tester = $this->container->getOutlineTesterService();
            } elseif ($scenario instanceof ScenarioNode) {    
                $tester = $this->container->getScenarioTesterService();
            } else {
                throw new BehaviorException(
                    'Unknown scenario type found: ' . get_class($scenario)
                );
            }
            $result = max($result, $scenario->accept($tester));
        }

        $this->dispatcher->notify(new Event($feature, 'feature.run.after', array(
            'result' => $result
        )));

        return $result;
    }
}
