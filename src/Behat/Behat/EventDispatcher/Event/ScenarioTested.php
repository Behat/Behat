<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;

/**
 * Represents a scenario event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
interface ScenarioTested extends LifecycleEvent, GherkinNodeTested
{
    public const BEFORE = 'tester.scenario_tested.before';
    public const AFTER_SETUP = 'tester.scenario_tested.after_setup';
    public const BEFORE_TEARDOWN = 'tester.scenario_tested.before_teardown';
    public const AFTER = 'tester.scenario_tested.after';

    /**
     * Returns feature.
     */
    public function getFeature(): FeatureNode;

    /**
     * Returns scenario node.
     */
    public function getScenario(): ScenarioLikeInterface;
}
