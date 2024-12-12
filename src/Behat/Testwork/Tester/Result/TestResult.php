<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result;

/**
 * Represents a test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface TestResult
{
    public const PASSED = 0;
    public const SKIPPED = 10;
    public const PENDING = 20;
    public const UNDEFINED = 30;
    public const FAILED = 99;

    /**
     * Checks that test has passed.
     *
     * @return bool
     */
    public function isPassed();

    /**
     * Returns tester result code.
     *
     * @return integer
     */
    public function getResultCode();
}
