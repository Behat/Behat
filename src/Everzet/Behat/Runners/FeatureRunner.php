<?php

namespace Everzet\Behat\Runners;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Feature;
use \Everzet\Gherkin\Structures\Scenario\Scenario;
use \Everzet\Gherkin\Structures\Scenario\ScenarioOutline;
use \Everzet\Behat\Loggers\Logger;

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
class FeatureRunner extends BaseRunner implements Runner
{
    protected $feature;
    protected $container;
    protected $scenarioRunners = array();

    public function __construct(Feature $feature, Container $container, Logger $logger)
    {
        $this->feature      = $feature;
        $this->container    = $container;
        $this->setLogger(     $logger);

        foreach ($feature->getScenarios() as $scenario) {
            if ($scenario instanceof ScenarioOutline) {
                $this->scenarioRunners[] = new ScenarioOutlineRunner(
                    $scenario
                  , $feature->getBackground()
                  , $container
                  , $logger
                );
            } else {
                $this->scenarioRunners[] = new ScenarioRunner(
                    $scenario
                  , $feature->getBackground()
                  , $container
                  , $logger
                );
            }
        }
    }

    public function getFeature()
    {
        return $this->feature;
    }

    public function run(Runner $caller = null)
    {
        $this->setCaller($caller);
        $this->getLogger()->beforeFeature($this);

        foreach ($this->scenarioRunners as $runner) {
            $runner->run($this);
        }

        $this->getLogger()->afterFeature($this);
    }
}
