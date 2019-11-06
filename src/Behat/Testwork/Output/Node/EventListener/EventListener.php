<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Node\EventListener;

use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;

/**
 * Used to define formatter event listeners.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface EventListener
{
    /**
     * Notifies listener about an event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     * @param string    $eventName
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName);
}
