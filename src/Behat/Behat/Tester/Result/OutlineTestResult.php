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
 * Outline test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineTestResult extends TestResult
{
    /**
     * @var TestResults
     */
    private $exampleTestResults;

    /**
     * Initializes test result.
     *
     * @param TestResults $exampleTestResults
     */
    public function __construct(TestResults $exampleTestResults)
    {
        $this->exampleTestResults = $exampleTestResults;
    }

    /**
     * Returns example test results.
     *
     * @return TestResults
     */
    public function getExampleTestResults()
    {
        return $this->exampleTestResults;
    }

    /**
     * Returns tester result code.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->exampleTestResults->getResultCode();
    }
}
