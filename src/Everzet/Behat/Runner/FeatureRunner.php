<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Gherkin\Element\FeatureElement;
use Everzet\Gherkin\Element\Scenario\ScenarioElement;
use Everzet\Gherkin\Element\Scenario\ScenarioOutlineElement;

use Everzet\Behat\Exception\BehaviorException;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Feature runner.
 * Runs feature element tests.
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureRunner extends BaseRunner implements RunnerInterface
{
    protected $feature;

    /**
     * Creates runner instance
     *
     * @param   FeatureElement      $feature        parsed feature element
     * @param   Container           $container      dependency container
     * @param   RunnerInterface     $parent         parent runner
     */
    public function __construct(FeatureElement $feature, Container $container,
                                RunnerInterface $parent)
    {
        $this->feature  = $feature;
        $tagsFilter     = $container->getParameter('filter.tags');

        foreach ($feature->getScenarios() as $scenario) {
            if ($this->isScenarioSatisfiesTagEx($feature, $scenario, $tagsFilter)) {
                if ($scenario instanceof ScenarioOutlineElement) {
                    $this->addChildRunner(new ScenarioOutlineRunner(
                        $scenario
                      , $feature->getBackground()
                      , $container
                      , $this
                    ));
                } elseif ($scenario instanceof ScenarioElement) {
                    $this->addChildRunner(new ScenarioRunner(
                        $scenario
                      , $feature->getBackground()
                      , $container
                      , $this
                    ));
                } else {
                    throw new BehaviorException('Unknown scenario type: ' . get_class($scenario));
                }
            }
        }

        parent::__construct('feature', $container->getEventDispatcherService(), $parent);
    }

    /**
     * Checks if scenario satisfies tag filter expression
     *
     * @param   FeatureElement  $feature        parent feature
     * @param   ScenarioElement $scenario       scenario
     * @param   string          $tagExpression  expression
     * 
     * @return  boolean
     */
    protected function isScenarioSatisfiesTagEx(FeatureElement $feature, ScenarioElement $scenario, 
                                                $tagExpression)
    {
        if ($tagExpression) {
            $tags = explode(',', $tagExpression);

            $satisfies = false;

            foreach ($tags as $exp) {
                $exp = trim($exp);

                if ('~' === $exp[0]) {
                    $tag = substr($exp, 2);

                    if (!$scenario->hasTag($tag) && !$feature->hasTag($tag)) {
                        $satisfies = true;
                    }
                } else {
                    $tag = substr($exp, 1);

                    if ($scenario->hasTag($tag) || $feature->hasTag($tag)) {
                        $satisfies = true;
                    }
                }
            }

            return $satisfies;
        } else {
            return true;
        }
    }

    /**
     * Returns feature element
     *
     * @return  FeatureElement
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    protected function doRun()
    {
        $status = $this->statusToCode('passed');

        foreach ($this as $runner) {
            $status = max($status, $runner->run());
        }

        return $status;
    }
}
