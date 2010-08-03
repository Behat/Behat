<?php

namespace Everzet\Behat\Loggers\Base;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Behat\Loggers\Logger;
use \Everzet\Behat\Loggers\Base\ScenarioLogger;
use \Everzet\Behat\Runners\BackgroundRunner;
use \Everzet\Behat\Runners\StepRunner;

abstract class BackgroundLogger implements Logger
{
    protected $runner;
    protected $container;

    protected $scenarioLogger;
    protected $stepLoggers = array();

    public function __construct(BackgroundRunner $runner, Container $container)
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

    public function getStepLoggers()
    {
        return $this->stepLoggers;
    }

    public function run()
    {
        $this->before();
        $this->runner->run($this);
        $this->after();
    }

    public function setScenarioLogger(ScenarioLogger $logger)
    {
        $this->scenarioLogger = $logger;
    }

    public function logStep(StepRunner $runner)
    {
        $class  = $this->container->getParameter('logger.step.class');
        $logger = new $class($runner, $this->container);
        $logger->setScenarioLogger($this);

        return $this->stepLoggers[] = $logger;
    }

    public function before(){}
    public function after(){}
}
