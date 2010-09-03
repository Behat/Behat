<?php

namespace Everzet\Behat\Loggers;

use Symfony\Components\DependencyInjection\Container;

use \Everzet\Behat\Runners\FeatureRunner;
use \Everzet\Behat\Runners\ScenarioOutlineRunner;
use \Everzet\Behat\Runners\ScenarioRunner;
use \Everzet\Behat\Runners\BackgroundRunner;
use \Everzet\Behat\Runners\StepRunner;

interface Logger
{
    public function __construct(Container $container);

    public function beforeFeature(FeatureRunner $runner);
    public function afterFeature(FeatureRunner $runner);

    public function beforeScenarioOutline(ScenarioOutlineRunner $runner);
    public function afterScenarioOutline(ScenarioOutlineRunner $runner);

    public function beforeScenario(ScenarioRunner $runner);
    public function afterScenario(ScenarioRunner $runner);

    public function beforeBackground(BackgroundRunner $runner);
    public function afterBackground(BackgroundRunner $runner);

    public function beforeStep(StepRunner $runner);
    public function afterStep(StepRunner $runner);
}
