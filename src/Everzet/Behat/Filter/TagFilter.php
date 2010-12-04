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
 * Filters scenarios by feature/scenario tag.
 *
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TagFilter implements FilterInterface
{
    protected $filterString;

    /**
     * Set filtering string.
     * 
     * @param   string  $tags   tags filter string
     */
    public function setFilterString($filterString)
    {
        $this->filterString = $filterString;
    }

    /**
     * @see     Everzet\Behat\Filter\FilterInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('feature.run.filter_scenarios', array($this, 'filterScenarios'));
    }

    /**
     * Filter scenarios by tags.
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
        return $this->isClosuresMatchFilter(
            function($tag) use ($feature) {
                return $feature->hasTag($tag);
            }
          , function($tag) use ($feature) {
                return !$feature->hasTag($tag);
            }
          , null !== $filter ? $filter : $this->filterString
        );
    }

    /**
     * Check If Scenario Or Outline Matches Specified Filter. 
     * 
     * @param   ScenarioNode|OutlineNode    $scenario   scenario or outline
     * @param   string                      $filter     filter string (optional)
     */
    public function isScenarioMatchFilter($scenario, $filter = null)
    {
        $feature = $scenario->getFeature();

        return $this->isClosuresMatchFilter(
            function($tag) use ($feature, $scenario) {
                return $scenario->hasTag($tag) || $feature->hasTag($tag);
            }
          , function($tag) use ($feature, $scenario) {
                return !$scenario->hasTag($tag) && !$feature->hasTag($tag);
            }
          , null !== $filter ? $filter : $this->filterString
        );
    }

    /**
     * Check If Step Matches Specified Filter. 
     * 
     * @param   StepNode    $step       step
     * @param   string      $filter     filter string (optional)
     */
    public function isStepMatchFilter(StepNode $step, $filter = null)
    {
        $scenario   = $step->getParent();
        $feature    = $scenario->getFeature();

        return $this->isClosuresMatchFilter(
            function($tag) use ($feature, $scenario) {
                return $scenario->hasTag($tag) || $feature->hasTag($tag);
            }
          , function($tag) use ($feature, $scenario) {
                return !$scenario->hasTag($tag) && !$feature->hasTag($tag);
            }
          , null !== $filter ? $filter : $this->filterString
        );
    }

    /**
     * Check If Passed Has/Hasnt Closures Passes With Filter. 
     * 
     * @param   Closure $hasTagCheck    closure to check that something has got tag
     * @param   Closure $hasntTagCheck  closure to check that something hasn't got tag
     * @param   string  $filter         filter string
     */
    protected function isClosuresMatchFilter(\Closure $hasTagCheck, \Closure $hasntTagCheck, $filter)
    {
        $satisfies = true;

        foreach (explode('&&', $filter) as $andTags) {
            $satisfiesComma = false;

            foreach (explode(',', $andTags) as $tag) {
                $tag = str_replace('@', '', trim($tag));

                if ('~' === $tag[0]) {
                    $tag = mb_substr($tag, 1);
                    $satisfiesComma = $hasntTagCheck($tag) || $satisfiesComma;
                } else {
                    $satisfiesComma = $hasTagCheck($tag) || $satisfiesComma;
                }
            }

            $satisfies = (false !== $satisfiesComma && $satisfies && $satisfiesComma) || false;
        }

        return $satisfies;
    }
}
