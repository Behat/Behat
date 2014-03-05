<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\EventDispatcher;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Hook\EventDispatcher\HookedEventsSubscriber as BaseSubscriber;

/**
 * Behat hooked events subscriber.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookedEventsSubscriber extends BaseSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getHookableEvents()
    {
        return array(
            SuiteTested::BEFORE,
            SuiteTested::AFTER,
            FeatureTested::BEFORE,
            FeatureTested::AFTER,
            ScenarioTested::BEFORE,
            ScenarioTested::AFTER,
            ExampleTested::BEFORE,
            ExampleTested::AFTER,
            StepTested::BEFORE,
            StepTested::AFTER,
        );
    }
}
