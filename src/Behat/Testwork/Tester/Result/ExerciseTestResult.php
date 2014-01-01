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
 * Testwork exercise test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExerciseTestResult extends TestResult
{
    /**
     * @var TestResults
     */
    private $suiteTestResults;

    /**
     * Initializes test result.
     *
     * @param TestResults $suiteTestResults
     */
    public function __construct(TestResults $suiteTestResults)
    {
        $this->suiteTestResults = $suiteTestResults;
    }

    /**
     * Returns exercise suites tests results.
     *
     * @return SuiteTestResult[]
     */
    public function getSuiteTestResults()
    {
        return $this->suiteTestResults;
    }

    /**
     * Returns tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->suiteTestResults->getResultCode();
    }
}
