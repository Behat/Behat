<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Result;

use Behat\Testwork\Tester\Result\TestResults;

/**
 * Feature test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureTestResult extends TestResult
{
    /**
     * @var TestResults
     */
    private $scenarioTestResults;

    /**
     * Initializes test result.
     *
     * @param TestResults $scenarioTestResults
     */
    public function __construct(TestResults $scenarioTestResults)
    {
        $this->scenarioTestResults = $scenarioTestResults;
    }

    /**
     * Returns scenario tests results.
     *
     * @return TestResults
     */
    public function getScenarioTestResults()
    {
        return $this->scenarioTestResults;
    }

    /**
     * Returns tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->scenarioTestResults->getResultCode();
    }
}
