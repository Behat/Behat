<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Subject\SubjectIterator;
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
     * @var HookDispatcher
     */
    private $hookDispatcher;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param SubjectTester            $subjectTester
     * @param EnvironmentManager       $environmentManager
     * @param HookDispatcher           $hookDispatcher
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SubjectTester $subjectTester,
        EnvironmentManager $environmentManager,
        HookDispatcher $hookDispatcher,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->subjectTester = $subjectTester;
        $this->environmentManager = $environmentManager;
        $this->hookDispatcher = $hookDispatcher;
        $this->eventDispatcher = $eventDispatcher;
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
        $suite = $subjectIterator->getSuite();
        $environment = $this->buildEnvironment($subjectIterator->getSuite());

        $beforeHooks = $skip ? new CallResults() : $this->dispatchBeforeHooks($suite, $environment);
        $this->dispatchBeforeEvent($suite, $environment, $beforeHooks);

        $skip = $skip || $beforeHooks->hasExceptions();
        $result = $this->testSuite($environment, $suite, $subjectIterator, $beforeHooks, $skip);

        $afterHooks = $skip ? new CallResults() : $this->dispatchAfterHooks($suite, $environment, $result);
        $this->dispatchAfterEvent($suite, $environment, $result, $afterHooks);

        $result = new SuiteTestResult($result->getSubjectTestResults(), CallResults::merge($beforeHooks, $afterHooks));

        return new TestResult($result->getResultCode());
    }

    /**
     * Builds test an environment for a suite and subject.
     *
     * @param Suite $suite
     *
     * @return Environment
     */
    private function buildEnvironment(Suite $suite)
    {
        return $this->environmentManager->buildEnvironment($suite);
    }

    /**
     * Dispatches BEFORE event hooks.
     *
     * @param Suite       $suite
     * @param Environment $environment
     *
     * @return CallResults
     */
    private function dispatchBeforeHooks(Suite $suite, Environment $environment)
    {
        $event = new SuiteTested($suite, $environment);

        return $this->hookDispatcher->dispatchEventHooks(SuiteTested::BEFORE, $event);
    }

    /**
     * Dispatches BEFORE event.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param CallResults $hookCallResults
     */
    private function dispatchBeforeEvent(Suite $suite, Environment $environment, CallResults $hookCallResults)
    {
        $event = new SuiteTested($suite, $environment, null, $hookCallResults);

        $this->eventDispatcher->dispatch(SuiteTested::BEFORE, $event);
    }

    /**
     * Tests provided test subjects against provided environment.
     *
     * @param Environment     $environment
     * @param Suite           $suite
     * @param SubjectIterator $subjectIterator
     * @param CallResults     $hookResults
     * @param Boolean         $skip
     *
     * @return SuiteTestResult
     */
    private function testSuite(
        Environment $environment,
        Suite $suite,
        SubjectIterator $subjectIterator,
        CallResults $hookResults,
        $skip = false
    ) {
        $results = array();
        foreach ($subjectIterator as $subject) {
            $results[] = $this->testSubject($suite, $environment, $subject, $skip);
        }

        return new SuiteTestResult(new TestResults($results), $hookResults);
    }

    /**
     * Tests provided test subject against provided environment.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param mixed       $subject
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    private function testSubject(Suite $suite, Environment $environment, $subject, $skip = false)
    {
        return $this->subjectTester->test($suite, $environment, $subject, $skip);
    }

    /**
     * Dispatches AFTER event hooks.
     *
     * @param Suite           $suite
     * @param Environment     $environment
     * @param SuiteTestResult $result
     *
     * @return CallResults
     */
    private function dispatchAfterHooks(
        Suite $suite,
        Environment $environment,
        SuiteTestResult $result
    ) {
        $event = new SuiteTested($suite, $environment, $result);

        return $this->hookDispatcher->dispatchEventHooks(SuiteTested::AFTER, $event);
    }

    /**
     * Dispatches AFTER event.
     *
     * @param Suite           $suite
     * @param Environment     $environment
     * @param SuiteTestResult $result
     * @param CallResults     $hookCallResults
     */
    private function dispatchAfterEvent(
        Suite $suite,
        Environment $environment,
        SuiteTestResult $result,
        CallResults $hookCallResults
    ) {
        $this->eventDispatcher->dispatch(SuiteTested::AFTER, new SuiteTested($suite, $environment, $result, $hookCallResults));
    }
}
