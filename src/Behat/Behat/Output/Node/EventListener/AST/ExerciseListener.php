<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\AST;

use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\Output\Node\Printer\StatisticsPrinter;
use Behat\Behat\Output\Statistics\FailedHookStat;
use Behat\Behat\Output\Statistics\ScenarioStat;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Behat\Output\Statistics\StepStat;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Tester\Result\ExceptionResult;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\EventDispatcher\Event\BeforeTested;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Hook\Tester\Setup\HookedSetup;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;
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
     * @var ExceptionPresenter
     */
    private $exceptionPresenter;
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
     * @param StatisticsPrinter  $statisticsPrinter
     * @param ExceptionPresenter $exceptionPresenter
     */
    public function __construct(StatisticsPrinter $statisticsPrinter, ExceptionPresenter $exceptionPresenter)
    {
        $this->statisticsPrinter = $statisticsPrinter;
        $this->exceptionPresenter = $exceptionPresenter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->startTimerOnBeforeExercise($eventName);
        $this->captureCurrentFeaturePathOnBeforeFeatureEvent($event, $eventName);
        $this->forgetCurrentFeaturePathOnAfterFeatureEvent($eventName);
        $this->captureScenarioOrExampleStatsOnAfterEvent($event);
        $this->captureStepStatsOnAfterEvent($event, $eventName);
        $this->captureHookStatsOnEvent($event);

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
     * @param Event $event
     */
    private function captureScenarioOrExampleStatsOnAfterEvent(Event $event)
    {
        if (!$event instanceof AfterScenarioTested) {
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
        if (!$event instanceof AfterStepTested || StepTested::AFTER !== $eventName) {
            return;
        }

        $result = $event->getTestResult();
        $step = $event->getStep();
        $text = sprintf('%s %s', $step->getType(), $step->getText());
        $path = sprintf('%s:%d', $this->currentFeaturePath, $step->getLine());

        $error = null;
        $stdOut = null;

        if ($result instanceof ExceptionResult) {
            $error = $result->getException();
        }

        if ($error && $error instanceof PendingException) {
            $path = $event->getTestResult()->getStepDefinition()->getPath();
        }

        if ($error instanceof PendingException) {
            $error = $error->getMessage();
        } elseif ($error) {
            $error = $this->exceptionPresenter->presentException($error);
        }

        if ($result instanceof ExecutedStepResult) {
            $stdOut = $result->getCallResult()->getStdOut();
        }

        $resultCode = $result->getResultCode();
        $stat = new StepStat($text, $path, $resultCode, $error, $stdOut);

        $this->statistics->registerStepStat($stat);
    }

    /**
     * Captures hook stats on hooked event.
     *
     * @param Event $event
     */
    private function captureHookStatsOnEvent(Event $event)
    {
        if ($event instanceof BeforeTested && $event->getSetup() instanceof HookedSetup) {
            $this->captureBeforeHookStats($event->getSetup());
        }

        if ($event instanceof AfterTested && $event->getTeardown() instanceof HookedTeardown) {
            $this->captureAfterHookStats($event->getTeardown());
        }
    }

    /**
     * Captures before hook stats.
     *
     * @param HookedSetup $setup
     */
    private function captureBeforeHookStats(HookedSetup $setup)
    {
        $hookCallResults = $setup->getHookCallResults();

        if (!$hookCallResults->hasExceptions()) {
            return;
        }

        foreach ($hookCallResults as $hookCallResult) {
            $this->captureHookStat($hookCallResult);
        }
    }

    /**
     * Captures before hook stats.
     *
     * @param HookedTeardown $teardown
     */
    private function captureAfterHookStats(HookedTeardown $teardown)
    {
        $hookCallResults = $teardown->getHookCallResults();

        if (!$hookCallResults->hasExceptions()) {
            return;
        }

        foreach ($hookCallResults as $hookCallResult) {
            $this->captureHookStat($hookCallResult);
        }
    }

    /**
     * Captures hook call result.
     *
     * @param CallResult $hookCallResult
     */
    private function captureHookStat(CallResult $hookCallResult)
    {
        if (!$hookCallResult->hasException()) {
            return;
        }

        $callee = $hookCallResult->getCall()->getCallee();
        $hook = (string)$callee;
        $path = $callee->getPath();
        $stdOut = $hookCallResult->getStdOut();
        $error = null;
        if ($hookCallResult->getException()) {
            $error = $this->exceptionPresenter->presentException($hookCallResult->getException());
        }

        $stat = new FailedHookStat($hook, $path, $error, $stdOut);
        $this->statistics->registerFailedHookStat($stat);
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

        $this->timer->stop();
        $this->statisticsPrinter->printStatistics($formatter, $this->statistics, $this->timer, $this->memory);
    }
}
