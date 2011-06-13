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
     * @see Behat\Behat\Annotation\Annotation::__construct
     */
    public function __construct(array $callback, $filterString)
    {
        $methodRefl = new \ReflectionMethod($callback[0], $callback[1]);

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Callback should be valid callable');
        }

        if (!$methodRefl->isStatic()) {
            throw new \InvalidArgumentException('Feature hook callbacks should be static methods');
        }

        parent::__construct($callback);
    }

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
