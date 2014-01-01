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
 * Step container test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepContainerTestResult extends TestResult
{
    /**
     * @var TestResults
     */
    private $stepTestResults;
    /**
     * @var CallResults
     */
    private $hookCallResults;

    /**
     * Initializes test result.
     *
     * @param TestResults $stepTestResults
     * @param CallResults $hookCallResults
     */
    public function __construct(TestResults $stepTestResults, CallResults $hookCallResults)
    {
        $this->stepTestResults = $stepTestResults;
        $this->hookCallResults = $hookCallResults;
    }

    /**
     * Returns collection of all scenario steps tests results.
     *
     * @return TestResults
     */
    public function getStepTestResults()
    {
        return $this->stepTestResults;
    }

    /**
     * Returns hooks calls results.
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

        return $this->stepTestResults->getResultCode();
    }
}
