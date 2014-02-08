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
    private $specificationTestResults;

    /**
     * Initializes test result.
     *
     * @param TestResults $specificationTestResults
     */
    public function __construct(TestResults $specificationTestResults)
    {
        $this->specificationTestResults = $specificationTestResults;
    }

    /**
     * Returns all suite specification tests results.
     *
     * @return TestResults
     */
    public function getSpecificationTestResults()
    {
        return $this->specificationTestResults;
    }

    /**
     * Returns tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->specificationTestResults->getResultCode();
    }
}
