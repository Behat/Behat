<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents an event in which test was completed.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface AfterTested
{
    /**
     * Returns current test result.
     *
     * @return TestResult
     */
    public function getTestResult();

    /**
     * Returns current test teardown.
     *
     * @return Teardown
     */
    public function getTeardown();
}
