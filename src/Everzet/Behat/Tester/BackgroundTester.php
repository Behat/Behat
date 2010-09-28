<?php

namespace Everzet\Behat\Tester;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\NodeVisitorInterface;

use Everzet\Behat\Environment\EnvironmentInterface;

class BackgroundTester implements NodeVisitorInterface
{
    protected $container;
    protected $dispatcher;
    protected $environment;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->getEventDispatcherService();
    }

    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    public function visit($background)
    {
        $this->dispatcher->notify(new Event($background, 'background.run.before'));

        $result = 0;
        $skip   = false;

        // Visit & test steps
        foreach ($background->getSteps() as $step) {
            $tester = $this->container->getStepTesterService();
            $tester->setEnvironment($this->environment);
            $tester->skip($skip);

            $stResult = $step->accept($tester);

            if (0 !== $stResult) {
                $skip = true;
            }
            $result = max($result, $stResult);
        }

        $this->dispatcher->notify(new Event($background, 'background.run.after', array(
            'result'    => $result
          , 'skipped'   => $skip
        )));

        return $result;
    }
}
