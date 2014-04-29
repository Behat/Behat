<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\Flow;

use Behat\Behat\EventDispatcher\Event\AfterStepSetup;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
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
     * @var EventListener
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
     * @var Boolean
     */
    private $stepSetupHadOutput = false;

    /**
     * Initializes listener.
     *
     * @param EventListener $descendant
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
        if (!$this->firstBackgroundEnded) {
            return false;
        }

        return $event instanceof BackgroundTested || $this->isNonFailingConsequentBackgroundStep($event);
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
        if (!$this->inBackground) {
            return false;
        }

        return !$this->isStepEventWithOutput($event);
    }

    /**
     * Checks if provided event is a step event which setup or teardown produced any output.
     *
     * @param Event $event
     *
     * @return Boolean
     */
    private function isStepEventWithOutput(Event $event)
    {
        return $this->isBeforeStepEventWithOutput($event) || $this->isAfterStepWithOutput($event);
    }

    /**
     * Checks if provided event is a BEFORE step with setup that produced output.
     *
     * @param Event $event
     *
     * @return Boolean
     */
    private function isBeforeStepEventWithOutput(Event $event)
    {
        if ($event instanceof AfterStepSetup && $event->hasOutput()) {
            $this->stepSetupHadOutput = true;

            return true;
        }

        return false;
    }

    /**
     * Checks if provided event is an AFTER step with teardown that produced output.
     *
     * @param Event $event
     *
     * @return Boolean
     */
    private function isAfterStepWithOutput(Event $event)
    {
        if ($event instanceof AfterStepTested && ($this->stepSetupHadOutput || $event->hasOutput())) {
            $this->stepSetupHadOutput = false;

            return true;
        }

        return false;
    }
}
