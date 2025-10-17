<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\JSON;

use Behat\Behat\Output\Node\EventListener\Statistics\DurationListener;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Event\Event;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseSetup;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Suite\Suite;

final class JSONDurationListener extends DurationListener
{
    /**
     * @var array<string, Timer>
     */
    private array $suiteTimerStore = [];

    private Timer $exerciseTimer;

    /**
     * @var array<string, float>
     */
    private array $suiteResultStore = [];

    private ?float $exerciseResult = null;

    public function listenEvent(Formatter $formatter, Event $event, $eventName): void
    {
        parent::listenEvent($formatter, $event, $eventName);
        $this->captureExerciseStartEvent($event);
        $this->captureSuiteSetupEvent($event);
        $this->captureSuiteTeardownEvent($event);
        $this->captureExerciseEndEvent($event);
    }

    public function getSuiteDuration(Suite $suite): string
    {
        $key = $this->getHash($suite);

        return array_key_exists($key, $this->suiteResultStore)
            ? number_format($this->suiteResultStore[$key], 3, '.', '')
            : '';
    }

    public function getExerciseDuration(): string
    {
        return $this->exerciseResult !== null
            ? number_format($this->exerciseResult, 3, '.', '')
            : '';
    }

    private function captureExerciseStartEvent(Event $event): void
    {
        if ($event instanceof AfterExerciseSetup) {
            $this->exerciseTimer = $this->startTimer();
        }
    }

    private function captureExerciseEndEvent(Event $event): void
    {
        if ($event instanceof AfterExerciseCompleted) {
            $timer = $this->exerciseTimer;
            $timer->stop();
            $this->exerciseResult = $timer->getTime();
        }
    }

    private function captureSuiteSetupEvent(Event $event): void
    {
        if ($event instanceof BeforeSuiteTested) {
            $this->suiteTimerStore[$this->getHash($event->getSuite())] = $this->startTimer();
        }
    }

    private function captureSuiteTeardownEvent(Event $event): void
    {
        if ($event instanceof AfterSuiteTested) {
            $key = $this->getHash($event->getSuite());
            if (isset($this->suiteTimerStore[$key])) {
                $timer = $this->suiteTimerStore[$key];
                $timer->stop();
                $this->suiteResultStore[$key] = $timer->getTime();
                unset($this->suiteTimerStore[$key]);
            }
        }
    }
}
