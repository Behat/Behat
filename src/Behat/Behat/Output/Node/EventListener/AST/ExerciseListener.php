<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\AST;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\ScenarioLikeTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\Output\Node\Printer\StatisticsPrinter;
use Behat\Behat\Output\Statistics\ScenarioStat;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Behat\Output\Statistics\StepStat;
use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Behat exercise listener.
 *
 * Listens to entire exercise, collects statistics and then delegates it to the printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExerciseListener implements EventListener
{
    /**
     * @var StatisticsPrinter
     */
    private $statisticsPrinter;
    /**
     * @var Statistics
     */
    private $statistics;
    /**
     * @var Timer
     */
    private $timer;
    /**
     * @var Memory
     */
    private $memory;
    /**
     * @var string
     */
    private $currentFeaturePath;

    /**
     * Initializes listener.
     *
     * @param StatisticsPrinter $statisticsPrinter
     */
    public function __construct(StatisticsPrinter $statisticsPrinter)
    {
        $this->statisticsPrinter = $statisticsPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->startTimerOnBeforeExercise($eventName);
        $this->captureCurrentFeaturePathOnBeforeFeatureEvent($event, $eventName);
        $this->forgetCurrentFeaturePathOnAfterFeatureEvent($eventName);
        $this->captureScenarioOrExampleStatsOnAfterEvent($event, $eventName);
        $this->captureStepStatsOnAfterEvent($event, $eventName);

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

        $this->timer = new Timer();
        $this->timer->start();
        $this->memory = new Memory();
        $this->statistics = new Statistics();
    }

    /**
     * Captures current feature file path to the ivar on feature BEFORE event.
     *
     * @param Event  $event
     * @param string $eventName
     */
    private function captureCurrentFeaturePathOnBeforeFeatureEvent(Event $event, $eventName)
    {
        if (!$event instanceof FeatureTested || FeatureTested::BEFORE !== $eventName) {
            return;
        }

        $this->currentFeaturePath = $event->getFeature()->getFile();
    }

    /**
     * Removes current feature file path from the ivar on feature AFTER event.
     *
     * @param string $eventName
     */
    private function forgetCurrentFeaturePathOnAfterFeatureEvent($eventName)
    {
        if (FeatureTested::AFTER !== $eventName) {
            return;
        }

        $this->currentFeaturePath = null;
    }

    /**
     * Captures scenario or example stats on their AFTER event.
     *
     * @param Event  $event
     * @param string $eventName
     */
    private function captureScenarioOrExampleStatsOnAfterEvent(Event $event, $eventName)
    {
        if (!$event instanceof ScenarioLikeTested) {
            return;
        }

        if (!in_array($eventName, array(ScenarioTested::AFTER, ExampleTested::AFTER))) {
            return;
        }

        $line = $event->getScenario()->getLine();
        $resultCode = $event->getTestResult()->getResultCode();
        $stat = new ScenarioStat($this->currentFeaturePath, $line, $resultCode);

        $this->statistics->registerScenarioStat($stat);
    }

    /**
     * Captures step stats on step AFTER event.
     *
     * @param Event  $event
     * @param string $eventName
     */
    private function captureStepStatsOnAfterEvent(Event $event, $eventName)
    {
        if (!$event instanceof StepTested || StepTested::AFTER !== $eventName) {
            return;
        }

        $path = $event->getTestResult()->hasFoundDefinition()
            ? $event->getTestResult()->getSearchResult()->getMatchedDefinition()
            : null;

        $resultCode = $event->getTestResult()->getResultCode();
        $stat = new StepStat($path, $resultCode);

        $this->statistics->registerStepStat($stat);
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

        $this->statisticsPrinter->printStatistics($formatter, $this->statistics, $this->timer, $this->memory);
    }
}
