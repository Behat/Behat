<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Scenario\ScenarioOutline;
use \Everzet\Gherkin\Structures\Scenario\Background;
use \Everzet\Behat\Logger\LoggerInterface;

class ScenarioOutlineRunner extends BaseRunner implements RunnerInterface, \Iterator
{
    protected $outline;
    protected $background;
    protected $container;

    protected $position = 0;
    protected $scenarioRunners = array();

    public function __construct(ScenarioOutline $outline, Background $background = null, 
                                Container $container, LoggerInterface $logger)
    {
        $this->position     = 0;
        $this->outline      = $outline;
        $this->background   = $background;
        $this->container    = $container;
        $this->setLogger(     $logger);

        foreach ($this->outline->getExamples()->getTable()->getHash() as $tokens) {
            $runner = new ScenarioRunner(
                $this->outline
              , $this->background
              , $this->container
              , $this->logger
            );
            $runner->setTokens($tokens);

            $this->scenarioRunners[] = $runner;
        }
    }

    public function key()
    {
        return $this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->scenarioRunners[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->scenarioRunners[$this->position]);
    }

    public function getScenarioOutline()
    {
        return $this->outline;
    }

    public function run(RunnerInterface $caller = null)
    {
        $this->setCaller($caller);
        $this->getLogger()->beforeScenarioOutline($this);

        foreach ($this as $runner) {
            $runner->run($this);
        }

        $this->getLogger()->afterScenarioOutline($this);
    }
}
