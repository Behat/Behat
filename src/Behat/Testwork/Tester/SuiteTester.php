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
use Behat\Testwork\Subject\Subjects;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Event\SuiteTested;
use Behat\Testwork\Tester\Result\SuiteTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param SubjectTester            $subjectTester
     * @param EnvironmentManager       $environmentManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SubjectTester $subjectTester,
        EnvironmentManager $environmentManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->subjectTester = $subjectTester;
        $this->environmentManager = $environmentManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Tests provided suite subjects.
     *
     * @param Subjects $testSubjects
     * @param Boolean  $skip
     *
     * @return TestResult
     */
    public function test(Subjects $testSubjects, $skip = false)
    {
        $this->dispatchBeforeEvent($testSubjects->getSuite());
        $result = $this->testSuite($testSubjects, $skip);
        $this->dispatchAfterEvent($testSubjects->getSuite(), $result);

        return new TestResult($result->getResultCode());
    }

    /**
     * Dispatches BEFORE event.
     *
     * @param Suite $suite
     */
    private function dispatchBeforeEvent(Suite $suite)
    {
        $this->eventDispatcher->dispatch(SuiteTested::BEFORE, new SuiteTested($suite));
    }

    /**
     * Tests provided test subjects against provided environment.
     *
     * @param Subjects $testSubjects
     * @param Boolean  $skip
     *
     * @return SuiteTestResult
     */
    private function testSuite(Subjects $testSubjects, $skip = false)
    {
        $results = array();
        foreach ($testSubjects as $subject) {
            $environment = $this->buildEnvironment($testSubjects->getSuite(), $subject);
            $results[] = $this->testSubject($testSubjects->getSuite(), $environment, $subject, $skip);
        }

        return new SuiteTestResult(new TestResults($results));
    }

    /**
     * Builds test an environment for a suite and subject.
     *
     * @param Suite $suite
     * @param mixed $testSubject
     *
     * @return Environment
     */
    private function buildEnvironment(Suite $suite, $testSubject)
    {
        return $this->environmentManager->buildEnvironment($suite, $testSubject);
    }

    /**
     * Tests provided test subject against provided environment.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param mixed       $testSubject
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    private function testSubject(Suite $suite, Environment $environment, $testSubject, $skip = false)
    {
        return $this->subjectTester->test($suite, $environment, $testSubject, $skip);
    }

    /**
     * Dispatches AFTER event.
     *
     * @param Suite           $suite
     * @param SuiteTestResult $result
     */
    private function dispatchAfterEvent(Suite $suite, SuiteTestResult $result)
    {
        $this->eventDispatcher->dispatch(SuiteTested::AFTER, new SuiteTested($suite, $result));
    }
}
