<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Gherkin\Element\Scenario\ScenarioElement;
use Everzet\Gherkin\Element\Scenario\BackgroundElement;

class ScenarioRunner extends BaseRunner implements RunnerInterface
{
    protected $scenario;
    protected $definitions;

    protected $skip = false;
    protected $tokens = array();
    protected $backgroundRunner;

    public function __construct(ScenarioElement $scenario, BackgroundElement $background = null,
                                Container $container, RunnerInterface $parent)
    {
        $this->scenario     = $scenario;
        $this->definitions  = $container->getStepsLoaderService();

        if (null !== $background) {
            $this->backgroundRunner = new BackgroundRunner(
                $background
              , $this->definitions
              , $container
              , $this
            );
        }

        foreach ($scenario->getSteps() as $step) {
            $this->addChildRunner(new StepRunner($step, $this->definitions, $container, $this));
        }

        parent::__construct('scenario', $container->getEventDispatcherService(), $parent);
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
        return $this->getParentRunner() instanceof ScenarioOutlineRunner;
    }

    public function getExceptions()
    {
        $exceptions = array();

        foreach ($this->childs as $stepRunner) {
            if (null !== $stepRunner->getException()) {
                $exceptions[] = $stepRunner->getException();
            }
        }

        return $exceptions;
    }

    protected function doRun()
    {
        if (null !== $this->backgroundRunner) {
            $this->backgroundRunner->run();
        }

        foreach ($this as $runner) {
            if (null !== $this->tokens && count($this->tokens)) {
                $runner->setTokens($this->tokens);
            }

            if (!$this->skip) {
                if ('passed' !== $runner->run()) {
                    $this->skip = true;
                }
            } else {
                $runner->skip();
            }
        }
    }
}
