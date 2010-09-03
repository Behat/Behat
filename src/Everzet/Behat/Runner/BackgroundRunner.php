<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Gherkin\Element\Scenario\BackgroundElement;

use Everzet\Behat\Loader\StepsLoader;
use Everzet\Behat\Logger\LoggerInterface;

class BackgroundRunner extends BaseStepsRunner implements RunnerInterface
{
    protected $background;
    protected $definitions;
    protected $container;

    public function __construct(BackgroundElement $background, StepsLoader $definitions, 
                                Container $container, LoggerInterface $logger)
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

    public function run(RunnerInterface $caller = null)
    {
        $this->setCaller($caller);
        $this->getLogger()->beforeBackground($this);

        foreach ($this as $runner) {
            $runner->run($this);
        }

        $this->getLogger()->afterBackground($this);
    }
}
