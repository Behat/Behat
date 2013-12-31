<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Event;

use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\SuiteTestResult;
use Symfony\Component\EventDispatcher\Event;

/**
 * Testwork suite tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteTested extends Event
{
    const BEFORE = 'tester.suite_tested.before';
    const AFTER = 'tester.suite_tested.after';

    /**
     * @var Suite
     */
    private $suite;
    /**
     * @var null|SuiteTestResult
     */
    private $testResult;

    /**
     * Initializes event.
     *
     * @param Suite                $suite
     * @param null|SuiteTestResult $testResult
     */
    public function __construct(Suite $suite, SuiteTestResult $testResult = null)
    {
        $this->suite = $suite;
        $this->testResult = $testResult;
    }

    /**
     * Returns suite in which this event was fired.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Returns suite test result (if tested).
     *
     * @return null|SuiteTestResult
     */
    public function getTestResult()
    {
        return $this->testResult;
    }

    /**
     * Returns step tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->testResult->getResultCode();
    }
}
