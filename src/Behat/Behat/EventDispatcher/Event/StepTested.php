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
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;

/**
 * Represents a step event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class StepTested extends LifecycleEvent implements GherkinNodeTested
{
    const BEFORE = 'tester.step_tested.before';
    const AFTER = 'tester.step_tested.after';

    /**
     * Returns feature.
     *
     * @return FeatureNode
     */
    abstract public function getFeature();

    /**
     * Returns step node.
     *
     * @return StepNode
     */
    abstract public function getStep();

    /**
     * Checks if step call, setup or teardown produced any output (stdOut or exception).
     *
     * @return Boolean
     */
    abstract public function hasOutput();

    /**
     * {@inheritdoc}
     */
    final public function getNode()
    {
        return $this->getStep();
    }
}
