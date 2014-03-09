<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Behat\Tester\Result\StepResult;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat result code to string converter.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ResultToStringConverter
{
    /**
     * Converts provided test result to a string.
     *
     * @param TestResult $result
     *
     * @return string
     */
    public function convertResultToString(TestResult $result)
    {
        return $this->convertResultCodeToString($result->getResultCode());
    }

    /**
     * Converts provided result code to a string.
     *
     * @param integer $resultCode
     *
     * @return string
     */
    public function convertResultCodeToString($resultCode)
    {
        switch ($resultCode) {
            case TestResult::SKIPPED:
                return 'skipped';
            case TestResult::PENDING:
                return 'pending';
            case TestResult::FAILED:
                return 'failed';
            case StepResult::UNDEFINED:
                return 'undefined';
        }

        return 'passed';
    }
}
