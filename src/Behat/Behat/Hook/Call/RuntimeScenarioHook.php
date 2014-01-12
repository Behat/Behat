<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Tester\Event\AbstractScenarioTested;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\Hook\Call\RuntimeFilterableHook;
use Behat\Testwork\Hook\Event\LifecycleEvent;

/**
 * Runtime scenario hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeScenarioHook extends RuntimeFilterableHook
{
    /**
     * Checks if provided event matches hook filter.
     *
     * @param LifecycleEvent $event
     *
     * @return Boolean
     */
    public function filterMatches(LifecycleEvent $event)
    {
        if (!$event instanceof AbstractScenarioTested) {
            return false;
        }
        if (null === ($filterString = $this->getFilterString())) {
            return true;
        }

        return $this->isMatch($event->getFeature(), $event->getScenario(), $filterString);
    }

    /**
     * Checks if nodes match filter.
     *
     * @param FeatureNode       $feature
     * @param ScenarioInterface $scenario
     * @param string            $filterString
     *
     * @return Boolean
     */
    protected function isMatch(FeatureNode $feature, ScenarioInterface $scenario, $filterString)
    {
        if (false !== strpos($filterString, '@')) {
            return $this->isMatchTagFilter($feature, $scenario, $filterString);
        }

        if (!empty($filterString)) {
            return $this->isMatchNameFilter($scenario, $filterString);
        }

        return false;
    }

    /**
     * Checks if node match tag filter.
     *
     * @param FeatureNode       $feature
     * @param ScenarioInterface $scenario
     * @param string            $filterString
     *
     * @return Boolean
     */
    protected function isMatchTagFilter(FeatureNode $feature, ScenarioInterface $scenario, $filterString)
    {
        $filter = new TagFilter($filterString);

        if ($filter->isFeatureMatch($feature)) {
            return true;
        }

        return $filter->isScenarioMatch($feature, $scenario);
    }

    /**
     * Checks if scenario matches name filter.
     *
     * @param ScenarioInterface $scenario
     * @param string            $filterString
     *
     * @return Boolean
     */
    protected function isMatchNameFilter(ScenarioInterface $scenario, $filterString)
    {
        $filter = new NameFilter($filterString);

        return $filter->isScenarioMatch($scenario);
    }
}
