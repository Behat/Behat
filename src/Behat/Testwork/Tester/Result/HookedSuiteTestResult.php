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
 * Testwork hooked suite test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookedSuiteTestResult extends SuiteTestResult
{
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
        parent::__construct($subjectTestResults);

        $this->hookCallResults = $hookCallResults;
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

        return parent::getResultCode();
    }
}
