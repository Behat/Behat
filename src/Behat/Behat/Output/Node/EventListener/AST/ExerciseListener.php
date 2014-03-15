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
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\Output\Statistics\FailedHookStat;
use Behat\Behat\Output\Statistics\ScenarioStat;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Behat\Output\Statistics\StepStat;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\EventDispatcher\Event\BeforeTested;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Hook\Tester\Setup\HookedSetup;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Exception;
use Symfony\Component\EventDispatcher\Event;

/**
 * Listens to entire exercise, collects statistics and then delegates it to the printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ExerciseListener implements EventListener
{
    /**
     * @var Statistics
     */
    private $statistics;
    /**
     * @var ExceptionPresenter
     */
    private $exceptionPresenter;
    /**
     * @var string
     */
    private $currentFeaturePath;

    /**
     * Initializes listener.
     *
     * @param Statistics         $statistics
     * @param ExceptionPresenter $exceptionPresenter
     */
    public function __construct(Statistics $statistics, ExceptionPresenter $exceptionPresenter)
    {
        $this->statistics = $statistics;
        $this->exceptionPresenter = $exceptionPresenter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->captureCurrentFeaturePathOnBeforeFeatureEvent($event);
        $this->forgetCurrentFeaturePathOnAfterFeatureEvent($eventName);
        $this->captureScenarioOrExampleStatsOnAfterEvent($event);
        $this->captureStepStatsOnAfterEvent($event);
        $this->captureHookStatsOnEvent($event);
    }

    /**
     * Captures current feature file path to the ivar on feature BEFORE event.
     *
     * @param Event $event
     */
    private function captureCurrentFeaturePathOnBeforeFeatureEvent(Event $event)
    {
        if (!$event instanceof BeforeFeatureTested) {
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
     * @param Event $event
     */
    private function captureStepStatsOnAfterEvent(Event $event)
    {
        if (!$event instanceof AfterStepTested) {
            return;
        }

        $result = $event->getTestResult();
        $step = $event->getStep();
        $text = sprintf('%s %s', $step->getType(), $step->getText());
        $exception = $this->getStepException($result);

        $path = $this->getStepPath($event, $exception);
        $error = $exception ? $this->exceptionPresenter->presentException($exception) : null;
        $stdOut = $result instanceof ExecutedStepResult ? $result->getCallResult()->getStdOut() : null;

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
        $error = $hookCallResult->getException()
            ? $this->exceptionPresenter->presentException($hookCallResult->getException())
            : null;

        $stat = new FailedHookStat($hook, $path, $error, $stdOut);
        $this->statistics->registerFailedHookStat($stat);
    }

    /**
     * Gets exception from the step test results.
     *
     * @param StepResult $result
     *
     * @return null|Exception
     */
    private function getStepException(StepResult $result)
    {
        if ($result instanceof ExceptionResult) {
            return $result->getException();
        }

        return null;
    }

    /**
     * Gets step path from the AFTER test event and exception.
     *
     * @param AfterStepTested $event
     * @param null|Exception  $exception
     *
     * @return string
     */
    private function getStepPath(AfterStepTested $event, Exception $exception = null)
    {
        $path = sprintf('%s:%d', $this->currentFeaturePath, $event->getStep()->getLine());

        if ($exception && $exception instanceof PendingException) {
            $path = $event->getTestResult()->getStepDefinition()->getPath();
        }

        return $path;
    }
}
