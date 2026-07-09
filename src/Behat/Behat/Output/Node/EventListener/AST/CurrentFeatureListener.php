<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\AST;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

/**
 * Keeps the feature currently being tested available for the whole feature run.
 *
 * Step results do not all carry a reference back to their feature - an undefined
 * step, for example, has no call result to read it from - so the feature node is
 * captured here once per feature and exposed for the duration of that feature.
 */
final class CurrentFeatureListener implements EventListener
{
    private ?FeatureNode $currentFeature = null;

    public function listenEvent(Formatter $formatter, Event $event, $eventName): void
    {
        if ($event instanceof BeforeFeatureTested) {
            $this->currentFeature = $event->getFeature();
        }

        if ($event instanceof AfterFeatureTested) {
            $this->currentFeature = null;
        }
    }

    public function getCurrentFeature(): ?FeatureNode
    {
        return $this->currentFeature;
    }
}
