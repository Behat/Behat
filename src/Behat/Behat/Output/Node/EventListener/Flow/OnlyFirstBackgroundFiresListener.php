<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\Flow;

use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\Tester\Result\StepTestResult;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Behat only first background fires listener.
 *
 * This listener catches all in-background events and then proxies them further
 * only if they meet one of two conditions:
 *
 *   1. It is a first background
 *   2. It is a failing step
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OnlyFirstBackgroundFiresListener implements EventListener
{
    /**
     * @var \Behat\Testwork\Output\Node\EventListener\EventListener
     */
    private $descendant;
    /**
     * @var Boolean
     */
    private $firstBackgroundEnded = false;
    /**
     * @var Boolean
     */
    private $inBackground = false;

    /**
     * Initializes listener.
     *
     * @param \Behat\Testwork\Output\Node\EventListener\EventListener $descendant
     */
    public function __construct(EventListener $descendant)
    {
        $this->descendant = $descendant;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->flushStatesIfBeginningOfTheFeature($eventName);
        $this->markBeginningOrEndOfTheBackground($eventName);

        if ($this->isSkippableEvent($event)) {
            return;
        }

        $this->markFirstBackgroundPrintedAfterBackground($eventName);

        $this->descendant->listenEvent($formatter, $event, $eventName);
    }

    /**
     * Flushes state if the event is the BEFORE feature.
     *
     * @param string $eventName
     */
    private function flushStatesIfBeginningOfTheFeature($eventName)
    {
        if (FeatureTested::BEFORE !== $eventName) {
            return;
        }

        $this->firstBackgroundEnded = false;
        $this->inBackground = false;
    }

    /**
     * Marks beginning or end of the background.
     *
     * @param string $eventName
     */
    private function markBeginningOrEndOfTheBackground($eventName)
    {
        if (BackgroundTested::BEFORE === $eventName) {
            $this->inBackground = true;
        }

        if (BackgroundTested::AFTER === $eventName) {
            $this->inBackground = false;
        }
    }

    /**
     * Marks first background printed.
     *
     * @param string $eventName
     */
    private function markFirstBackgroundPrintedAfterBackground($eventName)
    {
        if (BackgroundTested::AFTER !== $eventName) {
            return;
        }

        $this->firstBackgroundEnded = true;
    }

    /**
     * Checks if provided event is skippable.
     *
     * @param Event $event
     *
     * @return Boolean
     */
    private function isSkippableEvent(Event $event)
    {
        return $this->isConsequentBackgroundEvent($event) || $this->isNonFailingConsequentBackgroundStep($event);
    }

    /**
     * Checks if the provided event is a background event and is for 2+ background.
     *
     * @param Event $event
     *
     * @return Boolean
     */
    private function isConsequentBackgroundEvent(Event $event)
    {
        return $event instanceof BackgroundTested && $this->firstBackgroundEnded;
    }

    /**
     * Checks if provided event is a non-failing step in consequent background.
     *
     * @param Event $event
     *
     * @return Boolean
     */
    private function isNonFailingConsequentBackgroundStep(Event $event)
    {
        return $this->firstBackgroundEnded
            && $this->inBackground
            && $event instanceof StepTested
            && StepTestResult::FAILED !== $event->getResultCode();
    }
}
