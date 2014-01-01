<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Hook\Event\LifecycleEvent;

/**
 * Testwork hook repository.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookRepository
{
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * Initializes repository.
     *
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->environmentManager = $environmentManager;
    }

    /**
     * Returns all available hooks for a specific environment.
     *
     * @param Environment $environment
     *
     * @return Hook[]
     */
    public function getEnvironmentHooks(Environment $environment)
    {
        return array_filter(
            $this->environmentManager->readEnvironmentCallees($environment),
            function (Callee $callee) {
                return $callee instanceof Hook;
            }
        );
    }

    /**
     * Returns hooks for a specific event.
     *
     * @param string         $eventName
     * @param LifecycleEvent $event
     *
     * @return Hook[]
     */
    public function getEventHooks($eventName, LifecycleEvent $event)
    {
        return array_filter(
            $this->getEnvironmentHooks($event->getEnvironment()),
            function (Hook $hook) use ($eventName, $event) {
                if ($eventName !== $hook->getHookedEventName()) {
                    return false;
                }

                return !($hook instanceof FilterableHook && !$hook->filterMatches($event));
            }
        );
    }
}
