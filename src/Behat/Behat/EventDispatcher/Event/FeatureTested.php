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
use Behat\Gherkin\Node\NodeInterface;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;

/**
 * Represents a feature event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class FeatureTested extends LifecycleEvent implements GherkinNodeTested
{
    public const BEFORE = 'tester.feature_tested.before';
    public const AFTER_SETUP = 'tester.feature_tested.after_setup';
    public const BEFORE_TEARDOWN = 'tester.feature_tested.before_teardown';
    public const AFTER = 'tester.feature_tested.after';

    /**
     * Returns feature.
     *
     * @return FeatureNode
     */
    abstract public function getFeature();

    /**
     * Returns node.
     *
     * @return NodeInterface
     */
    final public function getNode()
    {
        return $this->getFeature();
    }
}
