<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Element\Scenario\ScenarioElement;
use Everzet\Gherkin\Element\Scenario\BackgroundElement;

class ScenarioRunner extends StepsRunner implements RunnerInterface
{
    protected $scenario;
    protected $definitions;
    protected $dispatcher;

    protected $tokens = array();
    protected $backgroundRunner;

    public function __construct(ScenarioElement $scenario, BackgroundElement $background = null,
                                Container $container)
    {
        $this->scenario     = $scenario;
        $this->definitions  = $container->getStepsLoaderService();
        $this->dispatcher   = $container->getEventDispatcherService();

        if (null !== $background) {
            $this->backgroundRunner = new BackgroundRunner(
                $background
              , $this->definitions
              , $container
            );
        }

        parent::__construct($scenario->getSteps(), $this->definitions, $container);
    }

    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function getScenario()
    {
        return $this->scenario;
    }

    public function isInOutline()
    {
        return $this->getCaller() instanceof ScenarioOutlineRunner;
    }

    public function getExceptions()
    {
        $exceptions = array();

        foreach ($this->getStepRunners() as $stepRunner) {
            if (null !== $stepRunner->getException()) {
                $exceptions[] = $stepRunner->getException();
            }
        }

        return $exceptions;
    }

    public function run(RunnerInterface $caller = null)
    {
        $this->setCaller($caller);
        $this->dispatcher->notify(new Event($this, 'scenario.pre_test'));

        if (null !== $this->backgroundRunner) {
            $this->backgroundRunner->run($this);
        }

        foreach ($this as $runner) {
            if (null !== $this->tokens && count($this->tokens)) {
                $runner->setTokens($this->tokens);
            }
            $this->runStepTest($runner);
        }

        $this->dispatcher->notify(new Event($this, 'scenario.post_test'));
    }
}
