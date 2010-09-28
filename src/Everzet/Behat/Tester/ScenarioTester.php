<?php

namespace Everzet\Behat\Tester;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\NodeVisitorInterface;

class ScenarioTester implements NodeVisitorInterface
{
    protected $container;
    protected $dispatcher;
    protected $environment;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->getEventDispatcherService();
        $this->environment  = $this->container->getEnvironmentService();
    }

    public function visit($scenario)
    {
        $this->dispatcher->notify(new Event($scenario, 'scenario.run.before'));

        $result = 0;
        $skip   = false;

        // Visit & test background if has one
        if ($scenario->getFeature()->hasBackground()) {
            $tester = $this->container->getBackgroundTesterService();
            $tester->setEnvironment($this->environment);

            $bgResult = $scenario->getFeature()->getBackground()->accept($tester);

            if (0 !== $bgResult) {
                $skip = true;
            }
            $result = max($result, $bgResult);
        }

        // Visit & test steps
        foreach ($scenario->getSteps() as $step) {
            $tester = $this->container->getStepTesterService();
            $tester->setEnvironment($this->environment);
            $tester->skip($skip);

            $stResult = $step->accept($tester);

            if (0 !== $stResult) {
                $skip = true;
            }
            $result = max($result, $stResult);
        }

        $this->dispatcher->notify(new Event($scenario, 'scenario.run.after', array(
            'result'    => $result
          , 'skipped'   => $skip
        )));

        return $result;
    }
}
