<?php
namespace Behat\Behat\Output\Node\EventListener\JUnit;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\KeywordNodeInterface;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

final class JUnitDurationListener implements EventListener
{
    /** @var array<string, Timer> */
    private $scenarioTimerStore = array();
    /** @var array<string, Timer> */
    private $featureTimerStore = array();
    /** @var array<string, float> */
    private $resultStore = array();
    /** @var array<string, float> */
    private $featureResultStore = array();

    /** @inheritdoc */
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
        $timer = $this->scenarioTimerStore[$key];
        if ($timer instanceof Timer) {
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
        $timer = $this->featureTimerStore[$key];
        if ($timer instanceof Timer) {
            $timer->stop();
            $this->featureResultStore[$key] = $timer->getTime();
            unset($this->featureTimerStore[$key]);
        }
    }

    private function getHash(KeywordNodeInterface $node): string
    {
        return spl_object_hash($node);
    }

    private function startTimer(): Timer
    {
        $timer = new Timer();
        $timer->start();

        return $timer;
    }
}
