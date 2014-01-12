<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Hook\Exception\BadCallbackException;
use Behat\Behat\Tester\Event\FeatureTested;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Hook\Call\RuntimeFilterableHook;
use Behat\Testwork\Hook\Event\LifecycleEvent;

/**
 * Runtime feature hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeFeatureHook extends RuntimeFilterableHook
{
    /**
     * Initializes hook.
     *
     * @param string      $eventName
     * @param null|string $filterString
     * @param callable    $callable
     * @param null|string $description
     *
     * @throws BadCallbackException If callback is method, but not a static one
     */
    public function __construct($eventName, $filterString, $callable, $description = null)
    {
        parent::__construct($eventName, $filterString, $callable, $description);

        if ($this->isAnInstanceMethod()) {
            throw new BadCallbackException(sprintf(
                'Feature hook callback: %s::%s() must be a static method',
                $callable[0],
                $callable[1]
            ), $callable);
        }
    }

    /**
     * Checks if provided event matches hook filter.
     *
     * @param LifecycleEvent $event
     *
     * @return Boolean
     */
    public function filterMatches(LifecycleEvent $event)
    {
        if (!$event instanceof FeatureTested) {
            return false;
        }

        if (null === ($filterString = $this->getFilterString())) {
            return true;
        }

        return $this->isMatch($event->getFeature(), $filterString);
    }

    /**
     * @param FeatureNode $feature
     * @param string $filterString
     *
     * @return Boolean
     */
    protected function isMatch(FeatureNode $feature, $filterString)
    {
        if (false !== strpos($filterString, '@')) {
            return $this->isMatchTagFilter($feature, $filterString);
        }

        if (!empty($filterString)) {
            return $this->isMatchNameFilter($feature, $filterString);
        }

        return false;
    }

    /**
     * Checks if feature matches tag filter.
     *
     * @param FeatureNode $feature
     * @param string $filterString
     *
     * @return Boolean
     */
    protected function isMatchTagFilter(FeatureNode $feature, $filterString)
    {
        $filter = new TagFilter($filterString);

        return $filter->isFeatureMatch($feature);
    }

    /**
     * Checks if feature matches name filter.
     *
     * @param FeatureNode $feature
     * @param string      $filterString
     *
     * @return Boolean
     */
    protected function isMatchNameFilter(FeatureNode $feature, $filterString)
    {
        $filter = new NameFilter($filterString);

        return $filter->isFeatureMatch($feature);
    }
}
