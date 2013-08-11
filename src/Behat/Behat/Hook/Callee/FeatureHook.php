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
use InvalidArgumentException;

/**
 * Base FeatureHook hook class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class FeatureHook extends FilterableHook
{
    /**
     * Initializes hook.
     *
     * @param string      $eventName
     * @param null|string $filterString
     * @param Callable    $callback
     * @param null|string $description
     *
     * @throws InvalidArgumentException If callback is method, but not a static one
     */
    public function __construct($eventName, $filterString, $callback, $description = null)
    {
        parent::__construct($eventName, $filterString, $callback, $description);

        if ($this->isMethod()) {
            $reflection = $this->getReflection();

            if (!$reflection->isStatic()) {
                throw new InvalidArgumentException(sprintf(
                    'Feature hook callback: %s::%s() must be a static method',
                    $callback[0],
                    $callback[1]
                ));
            }
        }
    }

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
