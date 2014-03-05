<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

/**
 * Behat outline example tester interface.
 *
 * This interface defines an API for Tree Outline Example testers.
 * Example tester is basically a scenario tester, because Outline Examples treated exactly like scenarios.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ExampleTester extends ScenarioTester
{
}
