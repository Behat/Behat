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
    public const BEFORE = 'tester.step_tested.before';
    public const AFTER_SETUP = 'tester.step_tested.after_setup';
    public const BEFORE_TEARDOWN = 'tester.step_tested.before_teardown';
    public const AFTER = 'tester.step_tested.after';

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
     * {@inheritdoc}
     */
    final public function getNode()
    {
        return $this->getStep();
    }
}
