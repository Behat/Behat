<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\Flow;

use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Behat fire only siblings listener.
 *
 * This listener catches all events, but proxies them to further listeners only if they
 * live inside specific event lifecycle (between BEFORE and AFTER events).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FireOnlySiblingsListener implements EventListener
{
    /**
     * @var string
     */
    private $beforeEventName;
    /**
     * @var string
     */
    private $afterEventName;
    /**
     * @var EventListener
     */
    private $descendant;
    /**
     * @var Boolean
     */
    private $inContext = false;

    /**
     * Initializes listener.
     *
     * @param string        $beforeEventName
     * @param string        $afterEventName
     * @param EventListener $descendant
     */
    public function __construct($beforeEventName, $afterEventName, EventListener $descendant)
    {
        $this->beforeEventName = $beforeEventName;
        $this->afterEventName = $afterEventName;
        $this->descendant = $descendant;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if ($this->beforeEventName === $eventName) {
            $this->inContext = true;
        }

        if ($this->inContext) {
            $this->descendant->listenEvent($formatter, $event, $eventName);
        }

        if ($this->afterEventName === $eventName) {
            $this->inContext = false;
        }
    }
}
