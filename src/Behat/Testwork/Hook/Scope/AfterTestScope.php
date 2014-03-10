<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Scope;

use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents a hook scope for After* hooks.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface AfterTestScope extends HookScope
{
    /**
     * Returns test result.
     *
     * @return TestResult
     */
    public function getTestResult();
}
