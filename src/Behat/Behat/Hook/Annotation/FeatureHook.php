<?php

namespace Behat\Behat\Hook\Annotation;

use Behat\Behat\Event\EventInterface;

use Behat\Gherkin\Filter\TagFilter,
    Behat\Gherkin\Filter\NameFilter;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * FeatureHook hook class.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class FeatureHook extends FilterableHook
{
    /**
     * {@inheritdoc}
     */
    public function filterMatches(EventInterface $event)
    {
        if (null === ($filterString = $this->getFilter())) {
            return true;
        }

        $feature = $event->getFeature();

        if (false !== strpos($filterString, '@')) {
            $filter = new TagFilter($filterString);

            if ($filter->isFeatureMatch($feature)) {
                return true;
            }
        } elseif (!empty($filterString)) {
            $filter = new NameFilter($filterString);

            if ($filter->isFeatureMatch($feature)) {
                return true;
            }
        }

        return false;
    }
}
