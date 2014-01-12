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
     * Initializes test result.
     *
     * @param TestResults $stepTestResults
     */
    public function __construct(TestResults $stepTestResults)
    {
        $this->stepTestResults = $stepTestResults;
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
     * Returns tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->stepTestResults->getResultCode();
    }
}
