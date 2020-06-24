<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Scope;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Hook\Scope\HookScope;

/**
 * Represents a step hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface StepScope extends HookScope
{
    public const BEFORE = 'step.before';
    public const AFTER = 'step.after';

    /**
     * Returns scope feature.
     *
     * @return FeatureNode
     */
    public function getFeature();

    /**
     * Returns scope step.
     *
     * @return StepNode
     */
    public function getStep();
}
