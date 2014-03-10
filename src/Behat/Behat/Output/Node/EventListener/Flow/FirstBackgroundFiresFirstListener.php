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
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Behat first background fires first listener.
 *
 * This listener catches first scenario and background events in the feature and makes sure
 * that background event are always fired before scenario events, thus following Gherkin format.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FirstBackgroundFiresFirstListener implements EventListener
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
     * @var Event[]
     */
    private $delayedUntilBackgroundEnd = array();

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
        $this->markFirstBackgroundPrintedAfterBackground($eventName);

        if ($this->isEventDelayedUntilFirstBackgroundPrinted($event)) {
            $this->delayedUntilBackgroundEnd[] = array($event, $eventName);

            return;
        }

        $this->descendant->listenEvent($formatter, $event, $eventName);
        $this->fireDelayedEventsOnAfterBackground($formatter, $eventName);
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
     * Checks if provided event should be postponed until background is printed.
     *
     * @param Event $event
     *
     * @return Boolean
     */
    private function isEventDelayedUntilFirstBackgroundPrinted(Event $event)
    {
        if (!$event instanceof ScenarioTested && !$event instanceof OutlineTested && !$event instanceof ExampleTested) {
            return false;
        }

        return !$this->firstBackgroundEnded && $event->getFeature()->hasBackground();
    }

    /**
     * Fires delayed events on AFTER background event.
     *
     * @param Formatter $formatter
     * @param string    $eventName
     */
    private function fireDelayedEventsOnAfterBackground(Formatter $formatter, $eventName)
    {
        if (BackgroundTested::AFTER !== $eventName) {
            return;
        }

        foreach ($this->delayedUntilBackgroundEnd as $eventInfo) {
            list($event, $eventName) = $eventInfo;

            $this->descendant->listenEvent($formatter, $event, $eventName);
        }

        $this->delayedUntilBackgroundEnd = array();
    }
}
