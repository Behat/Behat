<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Tester\Event\StepTested;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Hook\Call\RuntimeFilterableHook;
use Behat\Testwork\Hook\Event\LifecycleEvent;

/**
 * Runtime step hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeStepHook extends RuntimeFilterableHook
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
        if (!$event instanceof StepTested) {
            return false;
        }
        if (null === ($filterString = $this->getFilterString())) {
            return true;
        }

        if (!empty($filterString)) {
            $filter = new NameFilter($filterString);

            if ($filter->isFeatureMatch($event->getFeature())) {
                return true;
            }

            return $this->isStepMatch($event->getStep(), $filterString);
        }

        return false;
    }

    /**
     * Checks if Feature matches specified filter.
     *
     * @param StepNode $step Feature instance
     * @param string   $filterString
     *
     * @return Boolean
     */
    private function isStepMatch(StepNode $step, $filterString)
    {
        if ('/' === $filterString[0]) {
            return 1 === preg_match($filterString, $step->getText());
        }

        return false !== mb_strpos($step->getText(), $filterString, 0, 'utf8');
    }
}
