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
     * @var Statistics
     */
    private $statistics;
    /**
     * @var StatisticsPrinter
     */
    private $printer;

    /**
     * Initializes listener.
     *
     * @param Statistics        $statistics
     * @param StatisticsPrinter $statisticsPrinter
     */
    public function __construct(Statistics $statistics, StatisticsPrinter $statisticsPrinter)
    {
        $this->statistics = $statistics;
        $this->printer = $statisticsPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->startTimerOnBeforeExercise($eventName);
        $this->printStatisticsOnAfterExerciseEvent($formatter, $eventName);
    }

    /**
     * Starts timer on exercise BEFORE event.
     *
     * @param string $eventName
     */
    private function startTimerOnBeforeExercise($eventName)
    {
        if (ExerciseCompleted::BEFORE !== $eventName) {
            return;
        }

        $this->statistics->startTimer();
    }

    /**
     * Prints statistics on after exercise event.
     *
     * @param Formatter $formatter
     * @param string    $eventName
     */
    private function printStatisticsOnAfterExerciseEvent(Formatter $formatter, $eventName)
    {
        if (ExerciseCompleted::AFTER !== $eventName) {
            return;
        }

        $this->statistics->stopTimer();
        $this->printer->printStatistics($formatter, $this->statistics);
    }
}
