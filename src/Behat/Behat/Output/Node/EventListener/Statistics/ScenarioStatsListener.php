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
use Behat\Behat\Output\Statistics\ScenarioStat;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

/**
 * Listens and records scenario events to the statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ScenarioStatsListener implements EventListener
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
     * Initializes listener.
     *
     * @param Statistics $statistics
     */
    public function __construct(Statistics $statistics)
    {
        $this->statistics = $statistics;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->captureCurrentFeaturePathOnBeforeFeatureEvent($event);
        $this->forgetCurrentFeaturePathOnAfterFeatureEvent($event);
        $this->captureScenarioOrExampleStatsOnAfterEvent($event);
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
     * @param Event $event
     */
    private function forgetCurrentFeaturePathOnAfterFeatureEvent($event)
    {
        if (!$event instanceof AfterFeatureTested) {
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

        $scenario = $event->getScenario();
        $title = $scenario->getTitle();
        $path = sprintf('%s:%d', $this->currentFeaturePath, $scenario->getLine());
        $resultCode = $event->getTestResult()->getResultCode();

        $stat = new ScenarioStat($title, $path, $resultCode);
        $this->statistics->registerScenarioStat($stat);
    }
}
