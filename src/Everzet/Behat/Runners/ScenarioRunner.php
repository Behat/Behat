<?php

namespace Everzet\Behat\Runners;

use Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Scenario\Scenario;
use \Everzet\Gherkin\Structures\Scenario\Background;
use \Everzet\Behat\Loaders\StepsLoader;

class ScenarioRunner
{
    protected $scenario;
    protected $background;
    protected $container;
    protected $printer;

    public function __construct(Scenario $scenario, Background $background, Container $container)
    {
        $this->scenario = $scenario;
        $this->background = $background;
        $this->container = $container;
        $this->printer = $container->getPrinterService();
    }

    public function run()
    {
        $definitions = $this->container->getSteps_LoaderService();
        $this->printer->logScenarioBegin($this->scenario);
        $this->runBackground($definitions);
        foreach ($this->scenario->getSteps() as $step) {
            $runner = new StepRunner($step, $definitions, $this->container);
            $runner->run();
        }
        $this->printer->logScenarioEnd($this->scenario);
    }

    protected function runBackground(StepsLoader $definitions)
    {
        $this->printer->logBackgroundBegin($this->background);
        foreach ($this->background->getSteps() as $step) {
            $runner = new StepRunner($step, $definitions, $this->container);
            $runner->run();
        }
        $this->printer->logBackgroundEnd($this->background);
    }
}
