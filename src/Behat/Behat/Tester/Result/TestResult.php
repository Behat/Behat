<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Result;

use Behat\Testwork\Tester\Result\TestResult as BaseTestResult;

/**
 * Behat test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TestResult extends BaseTestResult
{
    const UNDEFINED = 30;

    /**
     * Converts result code into a string representation.
     *
     * @param integer $resultCode
     *
     * @return string
     */
    public static function codeToString($resultCode)
    {
        switch ($resultCode) {
            case self::SKIPPED:
                return 'skipped';
            case self::PENDING:
                return 'pending';
            case self::UNDEFINED:
                return 'undefined';
            case self::FAILED:
                return 'failed';
        }

        return 'passed';
    }
}
