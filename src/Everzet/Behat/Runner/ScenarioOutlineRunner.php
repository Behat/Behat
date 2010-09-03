<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Element\Scenario\ScenarioOutlineElement;
use Everzet\Gherkin\Element\Scenario\BackgroundElement;

class ScenarioOutlineRunner extends BaseRunner implements RunnerInterface, \Iterator
{
    protected $outline;
    protected $background;
    protected $dispatcher;

    protected $position = 0;
    protected $scenarioRunners = array();

    public function __construct(ScenarioOutlineElement $outline,
                                BackgroundElement $background = null, Container $container)
    {
        $this->position     = 0;
        $this->outline      = $outline;
        $this->background   = $background;
        $this->dispatcher   = $container->getEventDispatcherService();

        foreach ($this->outline->getExamples()->getTable()->getHash() as $tokens) {
            $runner = new ScenarioRunner(
                $this->outline
              , $this->background
              , $container
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
        $this->dispatcher->notify(new Event($this, 'scenario_outline.pre_test'));

        foreach ($this as $runner) {
            $runner->run($this);
        }

        $this->dispatcher->notify(new Event($this, 'scenario_outline.post_test'));
    }
}
