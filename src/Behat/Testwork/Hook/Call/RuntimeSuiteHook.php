<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Call;

use Behat\Testwork\Call\Exception\BadCallbackException;
use Behat\Testwork\Hook\Event\LifecycleEvent;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Event\SuiteTested;

/**
 * Runtime suite hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeSuiteHook extends RuntimeFilterableHook
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
                'Suite hook callback: %s::%s() must be a static method',
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
        if (!$event instanceof SuiteTested) {
            return false;
        }
        if (null === ($filterString = $this->getFilterString())) {
            return true;
        }

        if (!empty($filterString)) {
            return $this->isSuiteMatch($event->getSuite(), $filterString);
        }

        return false;
    }

    /**
     * Checks if Feature matches specified filter.
     *
     * @param Suite  $suite
     * @param string $filterString
     *
     * @return Boolean
     */
    private function isSuiteMatch(Suite $suite, $filterString)
    {
        if ('/' === $filterString[0]) {
            return 1 === preg_match($filterString, $suite->getName());
        }

        return false !== mb_strpos($suite->getName(), $filterString, 0, 'utf8');
    }
}
