<?php

namespace Behat\Behat\Hook\Annotation;

use Behat\Behat\Event\EventInterface;

use Behat\Gherkin\Filter\TagFilter,
    Behat\Gherkin\Filter\NameFilter,
    Behat\Gherkin\Node\BackgroundNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * StepHook hook class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class StepHook extends FilterableHook
{
    /**
     * {@inheritdoc}
     */
    public function filterMatches(EventInterface $event)
    {
        if (null === ($filterString = $this->getFilter())) {
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

    /**
     * Runs hook callback.
     *
     * @param EventInterface $event
     */
    public function run(EventInterface $event)
    {
        call_user_func($this->getCallbackForContext($event->getContext()), $event);
    }
}
