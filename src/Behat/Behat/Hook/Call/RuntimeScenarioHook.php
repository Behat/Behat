<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Tester\Event\ExampleTested;
use Behat\Behat\Tester\Event\ScenarioTested;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\TagFilter;
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
        if (!$event instanceof ScenarioTested && !$event instanceof ExampleTested) {
            return false;
        }
        if (null === ($filterString = $this->getFilterString())) {
            return true;
        }

        if ($event instanceof ScenarioTested) {
            $scenario = $event->getScenario();
        } else {
            $scenario = $event->getExample();
        }

        if (false !== strpos($filterString, '@')) {
            $filter = new TagFilter($filterString);

            if ($filter->isFeatureMatch($event->getSubject())) {
                return true;
            }
            if ($filter->isScenarioMatch($scenario)) {
                return true;
            }
        } elseif (!empty($filterString)) {
            $filter = new NameFilter($filterString);

            if ($filter->isScenarioMatch($scenario)) {
                return true;
            }
        }

        return false;
    }
}
