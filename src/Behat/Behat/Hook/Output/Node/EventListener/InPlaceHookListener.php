<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Output\Node\EventListener;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Testwork\Hook\EventDispatcher\Event\HookDispatched;
use Behat\Testwork\Hook\Output\Node\Printer\HookPrinter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Behat in-place hook listener.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class InPlaceHookListener implements EventListener
{
    /**
     * @var EventListener
     */
    private $descendant;
    /**
     * @var HookPrinter
     */
    private $printer;
    /**
     * @var array
     */
    private $postponedEvent;

    /**
     * Initializes listener.
     *
     * @param EventListener $descendant
     * @param HookPrinter   $printer
     */
    public function __construct(EventListener $descendant, HookPrinter $printer)
    {
        $this->descendant = $descendant;
        $this->printer = $printer;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if (in_array($eventName, array(ScenarioTested::BEFORE, ExampleTested::BEFORE, StepTested::BEFORE))) {
            $this->postponedEvent = array($event, $eventName);

            return;
        }

        if ($event instanceof HookDispatched && HookDispatched::AFTER === $eventName) {
            $this->printer->printHookResults(
                $formatter, $event->getHookedEventName(), $event->getHookedEvent(), $event->getCallResults()
            );

            if ($this->postponedEvent) {
                list($postponedEvent, $postponedEventName) = $this->postponedEvent;
                $this->descendant->listenEvent($formatter, $postponedEvent, $postponedEventName);
            }

            $this->postponedEvent = null;
        }

        $this->descendant->listenEvent($formatter, $event, $eventName);
    }
}
