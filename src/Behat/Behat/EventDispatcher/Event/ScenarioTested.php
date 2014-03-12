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
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;

/**
 * Represents a scenario event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class ScenarioTested extends LifecycleEvent implements ScenarioLikeTested
{
    const BEFORE = 'tester.scenario_tested.before';
    const AFTER = 'tester.scenario_tested.after';

    /**
     * Returns feature.
     *
     * @return FeatureNode
     */
    abstract public function getFeature();

    /**
     * Returns scenario node.
     *
     * @return ScenarioNode
     */
    abstract public function getScenario();

    /**
     * {@inheritdoc}
     */
    final public function getNode()
    {
        return $this->getScenario();
    }
}
