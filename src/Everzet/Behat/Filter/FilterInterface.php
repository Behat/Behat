<?php

namespace Everzet\Behat\Filter;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Everzet\Gherkin\Node\FeatureNode;
use Everzet\Gherkin\Node\ScenarioNode;
use Everzet\Gherkin\Node\StepNode;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Filter Interface.
 *
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface FilterInterface
{
    /**
     * Register listeners in filter.
     *
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function registerListeners(EventDispatcher $dispatcher);

    /**
     * Check If Feature Matches Specified Filter. 
     * 
     * @param   FeatureNode     $feature    feature
     * @param   string          $filter     filter string (optional)
     */
    public function isFeatureMatchFilter(FeatureNode $feature, $filter = null);

    /**
     * Check If Scenario Or Outline Matches Specified Filter. 
     * 
     * @param   ScenarioNode|OutlineNode    $scenario   scenario or outline
     * @param   string                      $filter     filter string (optional)
     */
    public function isScenarioMatchFilter($scenario, $filter = null);

    /**
     * Check If Step Matches Specified Filter. 
     * 
     * @param   StepNode    $step       step
     * @param   string      $filter     filter string (optional)
     */
    public function isStepMatchFilter(StepNode $step, $filter = null);
}
