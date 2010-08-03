<?php

namespace Everzet\Behat\Runners;

use Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Scenario\Background;
use \Everzet\Behat\Loaders\StepsLoader;
use \Everzet\Behat\Loggers\Base\BackgroundLogger;

class BackgroundRunner
{
    protected $background;
    protected $definitions;
    protected $container;

    public function __construct(Background $background, StepsLoader $definitions, 
                                Container $container)
    {
        $this->background   = $background;
        $this->definitions  = $definitions;
        $this->container    = $container;
    }

    public function getSubject()
    {
        return $this->background;
    }

    public function run(BackgroundLogger $logger)
    {
        foreach ($this->background->getSteps() as $step) {
            $runner = new StepRunner($step, $this->definitions, $this->container);
            $logger->logStep($runner)->run();
        }
    }
}
