<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\Statistics;

use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Behat\Output\Statistics\StepStat;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Exception;
use Symfony\Component\EventDispatcher\Event;

/**
 * Listens and records step events to statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StepStatsListener implements EventListener
{
    /**
     * @var Statistics
     */
    private $statistics;
    /**
     * @var string
     */
    private $currentFeaturePath;
    /**
     * @var ExceptionPresenter
     */
    private $exceptionPresenter;

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
        $this->captureStepStatsOnAfterEvent($event);
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
        $text = sprintf('%s %s', $step->getKeyword(), $step->getText());
        $exception = $this->getStepException($result);

        $path = $this->getStepPath($event, $exception);
        $error = $exception ? $this->exceptionPresenter->presentException($exception) : null;
        $stdOut = $result instanceof ExecutedStepResult ? $result->getCallResult()->getStdOut() : null;

        $resultCode = $result->getResultCode();
        $stat = new StepStat($text, $path, $resultCode, $error, $stdOut);

        $this->statistics->registerStepStat($stat);
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
