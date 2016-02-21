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
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Hook\Scope\HookScope;

/**
 * Represents a scenario hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ScenarioScope extends HookScope
{
    const BEFORE = 'scenario.before';
    const AFTER = 'scenario.after';

    /**
     * Returns scope feature.
     *
     * @return FeatureNode
     */
    public function getFeature();

    /**
     * Returns scenario.
     *
     * @return Scenario
     */
    public function getScenario();
}
