<?php

namespace Everzet\Behat\Runners;

use Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Scenario\Scenario;
use \Everzet\Gherkin\Structures\Scenario\Background;
use \Everzet\Behat\Runners\BackgroundRunner;
use \Everzet\Behat\Runners\StepRunner;
use \Everzet\Behat\Loggers\Base\ScenarioLogger;

class ScenarioRunner
{
    protected $scenario;
    protected $background;
    protected $container;
    protected $tokens = array();

    public function __construct(Scenario $scenario, Background $background = null,
                                Container $container)
    {
        $this->scenario     = $scenario;
        $this->background   = $background;
        $this->container    = $container;
    }

    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function getSubject()
    {
        return $this->scenario;
    }

    public function run(ScenarioLogger $logger)
    {
        $definitions = $this->container->getSteps_LoaderService();

        if (null !== $this->background) {
            $logger->logBackground(
                new BackgroundRunner($this->background, $definitions, $this->container)
            )->run();
        }

        foreach ($this->scenario->getSteps() as $step) {
            $runner = new StepRunner($step, $definitions, $this->container);
            if (count($this->tokens)) {
                $runner->setTokens($this->tokens);
            }
            $logger->logStep($runner)->run();
        }
    }
}
