<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Helper;

use Behat\Testwork\Tester\Result\TestResult;

/**
 * Converts result objects into a string representation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ResultToStringConverter
{
    /**
     * Converts provided test result to a string.
     */
    public function convertResultToString(TestResult $result): string
    {
        return $this->convertResultCodeToString($result->getResultCode());
    }

    /**
     * Converts provided result code to a string.
     *
     * @param int $resultCode
     */
    public function convertResultCodeToString($resultCode): string
    {
        return match ($resultCode) {
            TestResult::SKIPPED => 'skipped',
            TestResult::PENDING => 'pending',
            TestResult::FAILED => 'failed',
            TestResult::UNDEFINED => 'undefined',
            default => 'passed',
        };
    }
}
