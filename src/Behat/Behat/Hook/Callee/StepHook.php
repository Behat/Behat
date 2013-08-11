<?php

namespace Behat\Behat\Hook\Callee;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\TagFilter;

/**
 * Base StepHook hook class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class StepHook extends FilterableHook
{
    /**
     * Checks if provided event matches hook filter.
     *
     * @param EventInterface $event
     *
     * @return Boolean
     */
    public function filterMatches(EventInterface $event)
    {
        if (null === ($filterString = $this->getFilterString())) {
            return true;
        }

        $scenario = $event->getLogicalParent();

        if (false !== strpos($filterString, '@')) {
            $filter = new TagFilter($filterString);

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
