<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\EventDispatcher\Event;

use Behat\Testwork\Call\CallResults;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Testwork hook dispatched event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookDispatched extends Event
{
    const BEFORE = 'hook.hook_dispatched.before';
    const AFTER = 'hook.hook_dispatched.after';

    /**
     * @var string
     */
    private $hookedEventName;
    /**
     * @var LifecycleEvent
     */
    private $hookedEvent;
    /**
     * @var null|CallResults
     */
    private $callResults;

    /**
     * Initializes event.
     *
     * @param string           $hookedEventName
     * @param LifecycleEvent   $hookedEvent
     * @param null|CallResults $callResults
     */
    public function __construct($hookedEventName, LifecycleEvent $hookedEvent, CallResults $callResults = null)
    {
        $this->hookedEventName = $hookedEventName;
        $this->hookedEvent = $hookedEvent;
        $this->callResults = $callResults;
    }

    /**
     * Returns hooked event name.
     *
     * @return string
     */
    public function getHookedEventName()
    {
        return $this->hookedEventName;
    }

    /**
     * Returns hooked event.
     *
     * @return LifecycleEvent
     */
    public function getHookedEvent()
    {
        return $this->hookedEvent;
    }

    /**
     * Returns hook call results.
     *
     * @return null|CallResults
     */
    public function getCallResults()
    {
        return $this->callResults;
    }
}
