<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\Statistics;

use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Resets the StepStatCounter for each scenario.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class ResettingStepStatsListener implements EventListener
{
    /**
     * @var Statistics
     */
    private $statistics;

    /**
     * Initializes listener.
     *
     * @param Statistics         $statistics
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
        if ($event instanceof BeforeScenarioTested) {
            $this->statistics->resetStepCounter();
        }
    }
}
