<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\Statistics;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

class DurationListener implements EventListener
{
    /**
     * @var array<string, Timer>
     */
    private array $scenarioTimerStore = [];

    /**
     * @var array<string, Timer>
     */
    private array $featureTimerStore = [];

    /**
     * @var array<string, float>
     */
    private array $resultStore = [];

    /**
     * @var array<string, float>
     */
    private array $featureResultStore = [];

    public function listenEvent(Formatter $formatter, Event $event, $eventName): void
    {
        $this->captureBeforeScenarioEvent($event);
        $this->captureBeforeFeatureTested($event);
        $this->captureAfterScenarioEvent($event);
        $this->captureAfterFeatureEvent($event);
    }

    public function getDuration(ScenarioLikeInterface $scenario): string
    {
        $key = $this->getHash($scenario);

        return array_key_exists($key, $this->resultStore)
            ? number_format($this->resultStore[$key], 3, '.', '')
            : '';
    }

    public function getFeatureDuration(FeatureNode $feature): string
    {
        $key = $this->getHash($feature);

        return array_key_exists($key, $this->featureResultStore)
            ? number_format($this->featureResultStore[$key], 3, '.', '')
            : '';
    }

    private function captureBeforeFeatureTested(Event $event): void
    {
        if (!$event instanceof BeforeFeatureTested) {
            return;
        }

        $this->featureTimerStore[$this->getHash($event->getFeature())] = $this->startTimer();
    }

    private function captureBeforeScenarioEvent(Event $event): void
    {
        if (!$event instanceof BeforeScenarioTested) {
            return;
        }

        $this->scenarioTimerStore[$this->getHash($event->getScenario())] = $this->startTimer();
    }

    private function captureAfterScenarioEvent(Event $event): void
    {
        if (!$event instanceof AfterScenarioTested) {
            return;
        }

        $key = $this->getHash($event->getScenario());
        if (isset($this->scenarioTimerStore[$key])) {
            $timer = $this->scenarioTimerStore[$key];
            $timer->stop();
            $this->resultStore[$key] = $timer->getTime();
            unset($this->scenarioTimerStore[$key]);
        }
    }

    private function captureAfterFeatureEvent(Event $event): void
    {
        if (!$event instanceof AfterFeatureTested) {
            return;
        }

        $key = $this->getHash($event->getFeature());
        if (isset($this->featureTimerStore[$key])) {
            $timer = $this->featureTimerStore[$key];
            $timer->stop();
            $this->featureResultStore[$key] = $timer->getTime();
            unset($this->featureTimerStore[$key]);
        }
    }

    protected function getHash(object $node): string
    {
        return spl_object_hash($node);
    }

    protected function startTimer(): Timer
    {
        $timer = new Timer();
        $timer->start();

        return $timer;
    }
}
