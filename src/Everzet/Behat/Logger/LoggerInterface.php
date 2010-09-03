<?php

namespace Everzet\Behat\Logger;

use Symfony\Component\DependencyInjection\Container;

use \Everzet\Behat\Runner\FeatureRunner;
use \Everzet\Behat\Runner\ScenarioOutlineRunner;
use \Everzet\Behat\Runner\ScenarioRunner;
use \Everzet\Behat\Runner\BackgroundRunner;
use \Everzet\Behat\Runner\StepRunner;

interface LoggerInterface
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
