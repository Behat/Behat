<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Call;

use Behat\Testwork\Environment\Call\EnvironmentCall;
use Behat\Testwork\Hook\Event\LifecycleEvent;
use Behat\Testwork\Hook\Hook;

/**
 * Testwork hook call.
 *
 * Implements hook call.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookCall extends EnvironmentCall
{
    /**
     * @var LifecycleEvent
     */
    private $event;

    /**
     * Initializes hook call.
     *
     * @param LifecycleEvent $event
     * @param Hook           $hook
     * @param null|integer   $errorReportingLevel
     */
    public function __construct(LifecycleEvent $event, Hook $hook, $errorReportingLevel = null)
    {
        parent::__construct($event->getEnvironment(), $hook, array($event), $errorReportingLevel);

        $this->event = $event;
    }

    /**
     * Returns hooked event.
     *
     * @return LifecycleEvent
     */
    public function getEvent()
    {
        return $this->event;
    }
}
