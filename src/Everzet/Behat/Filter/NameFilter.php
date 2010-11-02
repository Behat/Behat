<?php

namespace Everzet\Behat\Filter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

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
 * Filters scenarios by feature/scenario name.
 *
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 */
class NameFilter implements FilterInterface
{
    protected $filterString;

    /**
     * Set filtering string.
     * 
     * @param   string  $tags   tags filter string
     */
    public function setFilterString($filterString)
    {
        $this->filterString = trim($filterString);
    }

    /**
     * @see     Everzet\Behat\Filter\FilterInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('feature.run.filter_scenarios', array($this, 'filterScenarios'));
    }

    /**
     * Filter scenarios by name.
     *
     * @param   Event   $event      filter event
     * @param   array   $scenarios  scenarios
     * 
     * @return  array               filtered scenarios
     */
    public function filterScenarios(Event $event, array $scenarios)
    {
        if (!empty($this->filterString)) {
            $filteredScenarios = array();

            foreach ($scenarios as $scenario) {
                if ($this->isScenarioMatchFilter($scenario)) {
                    $filteredScenarios[] = $scenario;
                }
            }

            return $filteredScenarios;
        }

        return $scenarios;
    }

    /**
     * Check If Feature Matches Specified Filter. 
     * 
     * @param   FeatureNode     $feature    feature
     * @param   string          $filter     filter string (optional)
     */
    public function isFeatureMatchFilter(FeatureNode $feature, $filter = null)
    {
        $filter = null !== $filter ? $filter : $this->filterString;

        if ('/' === $filter[0]) {
            return preg_match($filter, $feature->getTitle());
        }

        return false !== mb_strpos($feature->getTitle(), $filter);
    }

    /**
     * Check If Scenario Or Outline Matches Specified Filter. 
     * 
     * @param   ScenarioNode|OutlineNode    $scenario   scenario or outline
     * @param   string                      $filter     filter string (optional)
     */
    public function isScenarioMatchFilter($scenario, $filter = null)
    {
        $filter     = null !== $filter ? $filter : $this->filterString;
        $feature    = $scenario->getFeature();

        if ('/' === $filter[0]) {
            return preg_match($filter, $scenario->getTitle())
                || preg_match($filter, $feature->getTitle());
        }

        return false !== mb_strpos($scenario->getTitle(), $filter)
            || false !== mb_strpos($feature->getTitle(), $filter);
    }

    /**
     * Check If Step Matches Specified Filter. 
     * 
     * @param   StepNode    $step       step
     * @param   string      $filter     filter string (optional)
     */
    public function isStepMatchFilter(StepNode $step, $filter = null)
    {
        $filter     = null !== $filter ? $filter : $this->filterString;
        $scenario   = $step->getParent();
        $feature    = $scenario->getFeature();

        if ('/' === $filter[0]) {
            return preg_match($filter, $scenario->getTitle())
                || preg_match($filter, $feature->getTitle());
        }

        return false !== mb_strpos($scenario->getTitle(), $filter)
            || false !== mb_strpos($feature->getTitle(), $filter);
    }
}
