<?php

namespace Everzet\Behat\Loggers\Base;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Behat\Loggers\Logger;
use \Everzet\Behat\Loggers\Base\BackgroundLogger;
use \Everzet\Behat\Runners\StepRunner;

abstract class StepLogger implements Logger
{
    protected $runner;
    protected $container;

    protected $scenarioLogger;

    public function __construct(StepRunner $runner, Container $container)
    {
        $this->runner       = $runner;
        $this->container    = $container;
        $this->setup($container);
    }

    public function getRunner()
    {
        return $this->runner;
    }

    public function getStatus()
    {
        return $this->runner->getStatus();
    }

    public function getException()
    {
        return $this->runner->getException();
    }

    protected function setup(Container $container) {}

    public function run()
    {
        $this->runner->run();
        $this->after();
    }

    public function setScenarioLogger(BackgroundLogger $logger)
    {
        $this->scenarioLogger = $logger;
    }

    public function before(){}
    public function after(){}
}
