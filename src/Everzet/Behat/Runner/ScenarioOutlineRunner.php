<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Gherkin\Element\Scenario\ScenarioOutlineElement;
use Everzet\Gherkin\Element\Scenario\BackgroundElement;

class ScenarioOutlineRunner extends BaseRunner implements RunnerInterface, \Iterator
{
    protected $outline;
    protected $background;

    public function __construct(ScenarioOutlineElement $outline,
                                BackgroundElement $background = null, Container $container,
                                RunnerInterface $parent)
    {
        $this->outline      = $outline;
        $this->background   = $background;

        foreach ($outline->getExamples()->getTable()->getHash() as $tokens) {
            $runner = new ScenarioRunner($outline, $background, $container, $this);
            $runner->setTokens($tokens);
            $this->addChildRunner($runner);
        }

        parent::__construct('scenario_outline', $container->getEventDispatcherService(), $parent);
    }

    public function getScenarioOutline()
    {
        return $this->outline;
    }

    protected function doRun()
    {
        $status = $this->statusToCode('passed');

        foreach ($this as $runner) {
            $status = max($status, $runner->run());
        }

        return $status;
    }
}
