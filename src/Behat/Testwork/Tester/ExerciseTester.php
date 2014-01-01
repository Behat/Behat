<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Subject\Subjects;
use Behat\Testwork\Tester\Event\ExerciseTested;
use Behat\Testwork\Tester\Result\ExerciseTestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Testwork Exercise tester.
 *
 * Tests exercises. Exercise is a set of test subject suites.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExerciseTester
{
    /**
     * @var SuiteTester
     */
    private $suiteTester;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param SuiteTester              $suiteTester
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(SuiteTester $suiteTester, EventDispatcherInterface $eventDispatcher)
    {
        $this->suiteTester = $suiteTester;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Tests suites subjects.
     *
     * @param Subjects[] $suitesSubjects
     * @param Boolean    $skip
     *
     * @return TestResult
     */
    public function test(array $suitesSubjects, $skip = false)
    {
        $this->dispatchBeforeEvent();
        $result = $this->testExercise($suitesSubjects, $skip);
        $this->dispatchAfterEvent($result);

        return new TestResult($result->getResultCode());
    }

    /**
     * Dispatches BEFORE event.
     */
    private function dispatchBeforeEvent()
    {
        $this->eventDispatcher->dispatch(ExerciseTested::BEFORE, new ExerciseTested());
    }

    /**
     * Tests provided suites.
     *
     * @param Subjects[] $suitesSubjects
     * @param Boolean    $skip
     *
     * @return ExerciseTestResult
     */
    private function testExercise(array $suitesSubjects, $skip = false)
    {
        $results = array();
        foreach ($suitesSubjects as $suiteSubjects) {
            $results[] = $this->testSuiteSubjects($suiteSubjects, $skip);
        }

        return new ExerciseTestResult(new TestResults($results));
    }

    /**
     * Tests provided suite subjects.
     *
     * @param Subjects $suiteSubjects
     * @param Boolean  $skip
     *
     * @return TestResult
     */
    private function testSuiteSubjects(Subjects $suiteSubjects, $skip = false)
    {
        return $this->suiteTester->test($suiteSubjects, $skip);
    }

    /**
     * Dispatched AFTER event.
     *
     * @param ExerciseTestResult $result
     */
    private function dispatchAfterEvent(ExerciseTestResult $result)
    {
        $this->eventDispatcher->dispatch(ExerciseTested::AFTER, new ExerciseTested($result));
    }
}
