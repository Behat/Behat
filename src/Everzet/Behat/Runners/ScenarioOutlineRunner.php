<?php

namespace Everzet\Behat\Runners;

use Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Scenario\ScenarioOutline;
use \Everzet\Gherkin\Structures\Scenario\Background;

class ScenarioOutlineRunner extends ScenarioRunner
{
    protected $outline;

    public function __construct(ScenarioOutline $outline, Background $background, 
                                Container $container)
    {
        $this->outline = $outline;
        $this->background = $background;
        $this->container = $container;
        $this->printer = $container->getPrinterService();
    }

    public function run()
    {
        $this->printer->logScenarioOutlineBegin($this->outline);
        foreach ($this->outline->getExamples() as $tokens) {
            $definitions = $this->container->getSteps_LoaderService();
            $this->runBackground($definitions);
            foreach ($this->outline->getSteps() as $step) {
                $runner = new StepRunner($step, $definitions, $this->container);
                $runner->setTokens($tokens);
                $runner->setIsInOutline(true);
                $runner->run();
            }
            $this->printer->logIntermediateOutlineScenario($this->outline);
        }
        $this->printer->logScenarioOutlineEnd($this->outline);
    }
}
