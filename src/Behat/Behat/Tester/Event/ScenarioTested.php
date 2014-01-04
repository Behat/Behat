<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Event;

/**
 * Scenario event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioTested extends AbstractScenarioTested
{
    const BEFORE = 'tester.scenario_tested.before';
    const AFTER = 'tester.scenario_tested.after';
}
