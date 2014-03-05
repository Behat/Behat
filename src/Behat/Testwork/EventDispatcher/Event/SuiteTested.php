<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Testwork suite tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteTested extends LifecycleEvent
{
    const BEFORE = 'tester.suite_tested.before';
    const AFTER = 'tester.suite_tested.after';

    /**
     * @var null|TestResult
     */
    private $testResult;

    /**
     * Initializes event.
     *
     * @param Environment     $environment
     * @param null|TestResult $testResult
     */
    public function __construct(Environment $environment, TestResult $testResult = null)
    {
        parent::__construct($environment);

        $this->testResult = $testResult;
    }

    /**
     * Returns suite test result (if tested).
     *
     * @return null|TestResult
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
        if (null === $this->testResult) {
            return null;
        }

        return $this->testResult->getResultCode();
    }
}
