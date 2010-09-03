<?php

namespace Everzet\Behat\Runner;

use \Symfony\Component\DependencyInjection\Container;

use \Everzet\Gherkin\Element\FeatureElement;
use \Everzet\Gherkin\Element\Scenario\ScenarioElement;
use \Everzet\Gherkin\Element\Scenario\ScenarioOutlineElement;

use \Everzet\Behat\Exception\BehaviorException;
use \Everzet\Behat\Logger\LoggerInterface;

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
class FeatureRunner extends BaseRunner implements RunnerInterface
{
    protected $feature;
    protected $container;
    protected $scenarioRunners = array();

    public function __construct(FeatureElement $feature, Container $container,
                                LoggerInterface $logger)
    {
        $this->feature      = $feature;
        $this->container    = $container;
        $this->setLogger(     $logger);

        foreach ($feature->getScenarios() as $scenario) {
            if ($scenario instanceof ScenarioOutlineElement) {
                $this->scenarioRunners[] = new ScenarioOutlineRunner(
                    $scenario
                  , $feature->getBackground()
                  , $container
                  , $logger
                );
            } elseif ($scenario instanceof ScenarioElement) {
                $this->scenarioRunners[] = new ScenarioRunner(
                    $scenario
                  , $feature->getBackground()
                  , $container
                  , $logger
                );
            } else {
                throw new BehaviorException('Unknown scenario type: ' . get_class($scenario));
            }
        }
    }

    public function getFeature()
    {
        return $this->feature;
    }

    public function run(RunnerInterface $caller = null)
    {
        $this->setCaller($caller);
        $this->getLogger()->beforeFeature($this);

        foreach ($this->scenarioRunners as $runner) {
            $runner->run($this);
        }

        $this->getLogger()->afterFeature($this);
    }
}
