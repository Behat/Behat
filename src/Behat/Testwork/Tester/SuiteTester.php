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
use Behat\Testwork\Subject\SubjectIterator;
use Behat\Testwork\Tester\Result\SuiteTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;

/**
 * Testwork suite tester.
 *
 * Tests provided suites. Suite is a named set of test subjects.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteTester
{
    /**
     * @var SubjectTester
     */
    private $subjectTester;
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * Initializes tester.
     *
     * @param SubjectTester      $subjectTester
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(SubjectTester $subjectTester, EnvironmentManager $environmentManager)
    {
        $this->subjectTester = $subjectTester;
        $this->environmentManager = $environmentManager;
    }

    /**
     * Tests provided suite subjects.
     *
     * @param SubjectIterator $subjectIterator
     * @param Boolean         $skip
     *
     * @return TestResult
     */
    public function test(SubjectIterator $subjectIterator, $skip = false)
    {
        $environment = $this->environmentManager->buildEnvironment($subjectIterator->getSuite());
        $result = $this->testSuite($environment, $subjectIterator, $skip);

        return new TestResult($result->getResultCode());
    }

    /**
     * Tests provided test subjects against provided environment.
     *
     * @param Environment     $environment
     * @param SubjectIterator $iterator
     * @param Boolean         $skip
     *
     * @return SuiteTestResult
     */
    protected function testSuite(Environment $environment, SubjectIterator $iterator, $skip = false)
    {
        $results = array();
        foreach ($iterator as $subject) {
            $results[] = $this->subjectTester->test($environment, $subject, $skip);
        }

        return new SuiteTestResult(new TestResults($results));
    }
}
