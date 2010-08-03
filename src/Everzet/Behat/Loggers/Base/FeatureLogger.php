<?php

namespace Everzet\Behat\Loggers\Base;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Behat\Loggers\Logger;
use \Everzet\Behat\Runners\FeatureRunner;
use \Everzet\Behat\Runners\ScenarioOutlineRunner;
use \Everzet\Behat\Runners\ScenarioRunner;

abstract class FeatureLogger implements Logger
{    
    protected $runner;
    protected $container;

    protected $scenarioLoggers = array();

    public function __construct(FeatureRunner $runner, Container $container)
    {
        $this->runner       = $runner;
        $this->container    = $container;
        $this->setup($container);
    }

    public function getRunner()
    {
        return $this->runner;
    }

    protected function setup(Container $container) {}

    public function run()
    {
        $this->before();
        $this->runner->run($this);
        $this->after();
    }

    public function logScenario(ScenarioRunner $runner)
    {
        $class  = $this->container->getParameter('logger.scenario.class');
        $logger = new $class($runner, $this->container);
        $logger->setFeatureLogger($this);

        return $this->scenarioLoggers[] = $logger;
    }

    public function logScenarioOutline(ScenarioOutlineRunner $runner)
    {
        $class  = $this->container->getParameter('logger.scenario.outline.class');
        $logger = new $class($runner, $this->container);
        $logger->setFeatureLogger($this);

        return $this->scenarioLoggers[] = $logger;
    }

    public function before(){}
    public function after(){}
}
