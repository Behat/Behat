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

/**
 * Represents an event right before a teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface BeforeTeardown
{
    /**
     * Returns current test result.
     *
     * @return TestResult
     */
    public function getTestResult();
}
