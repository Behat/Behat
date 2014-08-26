<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\JUnit;

use Symfony\Component\EventDispatcher\Event;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Behat\Testwork\Output\Formatter;

/**
 * Listens for Outline events store the current one
 *
 * @author James Watson <james@sitepulse.org>
 */
class OutlineListener implements EventListener
{

    /**
     * @var OutlineNode
     */
    private $outline;

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->captureOutlineOnBeforeOutlineEvent($event);
        $this->forgetOutlineOnAfterOutlineEvent($eventName);
    }

    /**
     * Captures outline into the ivar on outline BEFORE event.
     *
     * @param Event $event
     */
    private function captureOutlineOnBeforeOutlineEvent(Event $event)
    {
        if (!$event instanceof BeforeOutlineTested) {
            return;
        }

        $this->outline = $event->getOutline();
    }

    /**
     * Removes the outline from the ivar on outline AFTER event
     *
     * @param string $eventName
     */
    private function forgetOutlineOnAfterOutlineEvent($eventName)
    {
        if (OutlineTested::AFTER !== $eventName) {
            return;
        }

        $this->outline = null;
    }

    /**
     * @return bool
     */
    public function inOutline()
    {
        return $this->outline instanceof OutlineNode;
    }

    /**
     * @return \Behat\Gherkin\Node\OutlineNode
     */
    public function getCurrentOutline()
    {
        return $this->outline;
    }
}