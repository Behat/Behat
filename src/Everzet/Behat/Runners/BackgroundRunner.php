<?php

namespace Everzet\Behat\Runners;

use Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Scenario\Background;
use \Everzet\Behat\Loaders\StepsLoader;
use \Everzet\Behat\Loggers\Logger;

class BackgroundRunner extends BaseStepsRunner implements Runner
{
    protected $background;
    protected $definitions;
    protected $container;

    public function __construct(Background $background, StepsLoader $definitions, 
                                Container $container, Logger $logger)
    {
        $this->background   = $background;
        $this->definitions  = $definitions;
        $this->container    = $container;
        $this->setLogger(     $logger);

        $this->initStepRunners(
            $this->background->getSteps()
          , $this->definitions
          , $this->container
          , $this->logger
        );
    }

    public function getBackground()
    {
        return $this->background;
    }

    public function run(Runner $caller = null)
    {
        $this->setCaller($caller);
        $this->getLogger()->beforeBackground($this);

        foreach ($this as $runner) {
            $runner->run($this);
        }

        $this->getLogger()->afterBackground($this);
    }
}
