<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Event;

use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Event\LifecycleEvent;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\SuiteTestResult;

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
     * @var null|SuiteTestResult
     */
    private $testResult;
    /**
     * @var null|CallResults
     */
    private $hookCallResults;

    /**
     * Initializes event.
     *
     * @param Suite                $suite
     * @param Environment          $environment
     * @param null|SuiteTestResult $testResult
     * @param null|CallResults     $hookCallResults
     */
    public function __construct(
        Suite $suite,
        Environment $environment,
        SuiteTestResult $testResult = null,
        CallResults $hookCallResults = null
    ) {
        parent::__construct($suite, $environment);

        $this->testResult = $testResult;
        $this->hookCallResults = $hookCallResults;
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
     * Returns hook call results.
     *
     * @return null|CallResults
     */
    public function getHookCallResults()
    {
        return $this->hookCallResults;
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
