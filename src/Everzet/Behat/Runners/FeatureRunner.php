<?php

namespace Everzet\Behat\Runners;

use Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Feature;
use \Everzet\Gherkin\Structures\Scenario\Scenario;
use \Everzet\Gherkin\Structures\Scenario\ScenarioOutline;
use \Everzet\Behat\Runners\ScenarioOutlineRunner;
use \Everzet\Behat\Runners\ScenarioRunner;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Runs specific feature
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureRunner
{
    protected $feature;
    protected $container;
    protected $scenarioRunners = array();

    public function __construct(Feature $feature, Container $container)
    {
        $this->feature = $feature;
        $this->container = $container;

        foreach ($feature->getScenarios() as $scenario) {
            if ($scenario instanceof ScenarioOutline) {
                $this->scenarioRunners[] = new ScenarioOutlineRunner(
                    $scenario, $feature->getBackground(), $container
                );
            } else {
                $this->scenarioRunners[] = new ScenarioRunner(
                    $scenario, $feature->getBackground(), $container
                );
            }
        }
    }

    public function run()
    {
        $printer = $this->container->getPrinterService();

        $printer->logFeatureBegin($this->feature);
        foreach ($this->scenarioRunners as $runner) {
            $runner->run();
        }
        $printer->logFeatureEnd($this->feature);
    }
}
