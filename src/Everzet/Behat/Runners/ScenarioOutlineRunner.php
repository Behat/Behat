<?php

namespace Everzet\Behat\Runners;

use Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Scenario\ScenarioOutline;
use \Everzet\Gherkin\Structures\Scenario\Background;
use \Everzet\Behat\Runners\ScenarioRunner;
use \Everzet\Behat\Loggers\Base\ScenarioOutlineLogger;

class ScenarioOutlineRunner
{
    protected $outline;
    protected $background;
    protected $container;

    public function __construct(ScenarioOutline $outline, Background $background = null, 
                                Container $container)
    {
        $this->outline      = $outline;
        $this->background   = $background;
        $this->container    = $container;
    }

    public function getSubject()
    {
        return $this->outline;
    }

    public function run(ScenarioOutlineLogger $logger)
    {
        foreach ($this->outline->getExamples()->getTable()->getHash() as $tokens) {
            $runner = new ScenarioRunner($this->outline, $this->background, $this->container);
            $runner->setTokens($tokens);
            $logger->logScenario($runner)->run();
        }
    }
}
