<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Base Testwork event.
 *
 * All testwork events must extend from this event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Event extends BaseEvent
{
    /**
     * Returns an event name.
     *
     * @return string
     */
    abstract public function getEventName();
}
