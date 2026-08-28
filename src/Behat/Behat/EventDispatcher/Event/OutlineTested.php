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
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;

/**
 * Represents a outline event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
interface OutlineTested extends LifecycleEvent, GherkinNodeTested
{
    public const BEFORE = 'tester.outline_tested.before';
    public const AFTER_SETUP = 'tester.outline_tested.after_setup';
    public const BEFORE_TEARDOWN = 'tester.outline_tested.before_teardown';
    public const AFTER = 'tester.outline_tested.after';

    /**
     * Returns feature.
     */
    public function getFeature(): FeatureNode;

    /**
     * Returns outline node.
     */
    public function getOutline(): OutlineNode;
}
