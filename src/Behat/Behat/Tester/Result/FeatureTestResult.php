<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Result;

use Behat\Testwork\Call\CallResults;
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
     * @var CallResults
     */
    private $hookCallResults;

    /**
     * Initializes test result.
     *
     * @param TestResults $scenarioTestResults
     * @param CallResults $hookCallResults
     */
    public function __construct(TestResults $scenarioTestResults, CallResults $hookCallResults)
    {
        $this->scenarioTestResults = $scenarioTestResults;
        $this->hookCallResults = $hookCallResults;
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

        return $this->scenarioTestResults->getResultCode();
    }
}
