<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result;

use Behat\Testwork\Call\CallResults;

/**
 * Testwork suite test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteTestResult extends TestResult
{
    /**
     * @var TestResults
     */
    private $subjectTestResults;
    /**
     * @var CallResults
     */
    private $hookCallResults;

    /**
     * Initializes test result.
     *
     * @param TestResults $subjectTestResults
     * @param CallResults $hookCallResults
     */
    public function __construct(TestResults $subjectTestResults, CallResults $hookCallResults)
    {
        $this->subjectTestResults = $subjectTestResults;
        $this->hookCallResults = $hookCallResults;
    }

    /**
     * Returns all suite subjects tests results.
     *
     * @return TestResults
     */
    public function getSubjectTestResults()
    {
        return $this->subjectTestResults;
    }

    /**
     * Returns scenarios hooks calls results.
     *
     * @return CallResults
     */
    public function getHookCallResults()
    {
        return $this->hookCallResults;
    }

    /**
     * Returns tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        if ($this->hookCallResults->hasExceptions()) {
            return static::FAILED;
        }

        return $this->subjectTestResults->getResultCode();
    }
}
