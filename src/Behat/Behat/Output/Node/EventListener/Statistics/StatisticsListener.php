<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\Statistics;

use Behat\Behat\Output\Node\Printer\StatisticsPrinter;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Testwork\Event\Event;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

/**
 * Collects general suite stats such as time and memory during its execution and prints it afterwards.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StatisticsListener implements EventListener
{
    /**
     * Initializes listener.
     */
    public function __construct(
        private readonly Statistics $statistics,
        private readonly StatisticsPrinter $printer,
    ) {
    }

    public function listenEvent(Formatter $formatter, Event $event, $eventName): void
    {
        $this->startTimerOnBeforeExercise($eventName);
        $this->printStatisticsOnAfterExerciseEvent($formatter, $eventName);
    }

    /**
     * Starts timer on exercise BEFORE event.
     *
     * @param string $eventName
     */
    private function startTimerOnBeforeExercise($eventName): void
    {
        if (ExerciseCompleted::BEFORE !== $eventName) {
            return;
        }

        $this->statistics->startTimer();
    }

    /**
     * Prints statistics on after exercise event.
     *
     * @param string    $eventName
     */
    private function printStatisticsOnAfterExerciseEvent(Formatter $formatter, $eventName): void
    {
        if (ExerciseCompleted::AFTER !== $eventName) {
            return;
        }

        $this->statistics->stopTimer();
        $this->printer->printStatistics($formatter, $this->statistics);
    }
}
