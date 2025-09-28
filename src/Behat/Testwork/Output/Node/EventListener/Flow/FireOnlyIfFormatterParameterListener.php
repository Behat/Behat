<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Node\EventListener\Flow;

use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

/**
 * Catches all events, but proxies them only if formatter has specific parameter set to a specific value.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FireOnlyIfFormatterParameterListener implements EventListener
{
    /**
     * Initializes listener.
     *
     * @param string        $name
     */
    public function __construct(
        private $name,
        private $value,
        private readonly EventListener $descendant,
    ) {
    }

    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if ($this->value !== $formatter->getParameter($this->name)) {
            return;
        }

        $this->descendant->listenEvent($formatter, $event, $eventName);
    }
}
