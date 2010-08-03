<?php

namespace Everzet\Behat\Loggers\Base;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Behat\Loggers\Logger;
use \Everzet\Behat\Loggers\Base\FeatureLogger;
use \Everzet\Behat\Loggers\Base\ScenarioOutlineLogger;
use \Everzet\Behat\Runners\ScenarioRunner;
use \Everzet\Behat\Runners\BackgroundRunner;
use \Everzet\Behat\Runners\StepRunner;

abstract class ScenarioLogger extends BackgroundLogger implements Logger
{
    protected $runner;
    protected $container;

    protected $featureLogger;
    protected $outlineLogger;
    protected $backgroundLogger;

    public function __construct(ScenarioRunner $runner, Container $container)
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

    public function inOutline()
    {
        return null !== $this->outlineLogger;
    }

    public function getStatus()
    {
        $status = 'passed';
        foreach ($this->stepLoggers as $stepLogger) {
            if ('passed' !== $stepLogger->getStatus()) {
                if ('failed' === $stepLogger->getStatus()) {
                    return 'failed';
                }
                $status = $stepLogger->getStatus();
            }
        }

        return $status;
    }

    public function getExceptions()
    {
        $exceptions = array();
        foreach ($this->stepLoggers as $stepLogger) {
            if (null !== $stepLogger->getException()) {
                $exceptions[] = $stepLogger->getException();
            }
        }

        return $exceptions;
    }

    public function setFeatureLogger(FeatureLogger $logger)
    {
        $this->featureLogger = $logger;
    }

    public function setOutlineLogger(ScenarioOutlineLogger $logger)
    {
        $this->outlineLogger = $logger;
    }

    public function logBackground(BackgroundRunner $runner)
    {
        $class  = $this->container->getParameter('logger.background.class');
        $logger = new $class($runner, $this->container);
        $logger->setScenarioLogger($this);

        return $this->backgroundLogger = $logger;
    }

    public function before(){}
    public function after(){}
}
