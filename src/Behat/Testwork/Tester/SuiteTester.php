<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Result\SuiteTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;

/**
 * Testwork suite tester.
 *
 * Tests provided suites. Suite is a named set of test specifications.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteTester
{
    /**
     * @var SpecificationTester
     */
    private $specificationTester;
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * Initializes tester.
     *
     * @param SpecificationTester $specificationTester
     * @param EnvironmentManager  $environmentManager
     */
    public function __construct(SpecificationTester $specificationTester, EnvironmentManager $environmentManager)
    {
        $this->specificationTester = $specificationTester;
        $this->environmentManager = $environmentManager;
    }

    /**
     * Tests provided suite specifications.
     *
     * @param SpecificationIterator $specificationIterator
     * @param Boolean               $skip
     *
     * @return TestResult
     */
    public function test(SpecificationIterator $specificationIterator, $skip = false)
    {
        $environment = $this->environmentManager->buildEnvironment($specificationIterator->getSuite());
        $result = $this->testSuite($environment, $specificationIterator, $skip);

        return new TestResult($result->getResultCode());
    }

    /**
     * Tests provided test specifications against provided environment.
     *
     * @param Environment           $environment
     * @param SpecificationIterator $iterator
     * @param Boolean               $skip
     *
     * @return SuiteTestResult
     */
    protected function testSuite(Environment $environment, SpecificationIterator $iterator, $skip = false)
    {
        $results = array();
        foreach ($iterator as $specification) {
            $results[] = $this->specificationTester->test($environment, $specification, $skip);
        }

        return new SuiteTestResult(new TestResults($results));
    }
}
