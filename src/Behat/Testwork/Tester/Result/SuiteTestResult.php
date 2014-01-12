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
     * Initializes test result.
     *
     * @param TestResults $subjectTestResults
     */
    public function __construct(TestResults $subjectTestResults)
    {
        $this->subjectTestResults = $subjectTestResults;
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
     * Returns tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->subjectTestResults->getResultCode();
    }
}
