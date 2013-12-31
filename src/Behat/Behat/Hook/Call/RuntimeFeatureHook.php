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
     * @param Callable    $callback
     * @param null|string $description
     *
     * @throws BadCallbackException If callback is method, but not a static one
     */
    public function __construct($eventName, $filterString, $callback, $description = null)
    {
        parent::__construct($eventName, $filterString, $callback, $description);

        if ($this->isAMethod()) {
            $reflection = $this->getReflection();

            if (!$reflection->isStatic()) {
                throw new BadCallbackException(sprintf(
                    'Feature hook callback: %s::%s() must be a static method',
                    $callback[0],
                    $callback[1]
                ), $callback);
            }
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

        $feature = $event->getSubject();

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
