<?php

namespace Everzet\Behat\Loggers\Base;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Behat\Loggers\Logger;
use \Everzet\Behat\Loggers\Base\FeatureLogger;
use \Everzet\Behat\Runners\ScenarioOutlineRunner;
use \Everzet\Behat\Runners\ScenarioRunner;

abstract class ScenarioOutlineLogger implements Logger
{
    protected $runner;
    protected $container;

    protected $featureLogger;
    protected $scenarioLoggers = array();

    public function __construct(ScenarioOutlineRunner $runner, Container $container)
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

    public function setFeatureLogger(FeatureLogger $logger)
    {
        $this->featureLogger = $logger;
    }

    public function logScenario(ScenarioRunner $runner)
    {
        $class  = $this->container->getParameter('logger.scenario.class');
        $logger = new $class($runner, $this->container);
        $logger->setOutlineLogger($this);

        return $this->scenarioLoggers[] = $logger;
    }

    public function before(){}
    public function after(){}
}
