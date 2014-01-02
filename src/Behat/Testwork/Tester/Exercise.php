<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Subject\Iterator\GroupedSubjectIterator;
use Behat\Testwork\Subject\Iterator\SubjectIterator;
use Behat\Testwork\Tester\Event\ExerciseCompleted;
use Behat\Testwork\Tester\Result\ExerciseTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Testwork exercise.
 *
 * Runs tests against all provided suites.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Exercise
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
     * @param SubjectIterator[] $subjectIterators
     * @param Boolean           $skip
     *
     * @return TestResult
     */
    public function run(array $subjectIterators, $skip = false)
    {
        $this->dispatchBeforeEvent();
        $result = $this->testExercise($subjectIterators, $skip);
        $this->dispatchAfterEvent($result);

        return new TestResult($result->getResultCode());
    }

    /**
     * Dispatches BEFORE event.
     */
    private function dispatchBeforeEvent()
    {
        $this->eventDispatcher->dispatch(ExerciseCompleted::BEFORE, new ExerciseCompleted());
    }

    /**
     * Tests provided suites.
     *
     * @param SubjectIterator[] $subjectIterators
     * @param Boolean           $skip
     *
     * @return ExerciseTestResult
     */
    private function testExercise(array $subjectIterators, $skip = false)
    {
        $results = array();
        foreach (GroupedSubjectIterator::group($subjectIterators) as $suiteSubjects) {
            $results[] = $this->testSuiteSubjects($suiteSubjects, $skip);
        }

        return new ExerciseTestResult(new TestResults($results));
    }

    /**
     * Tests provided suite subjects.
     *
     * @param SubjectIterator $subjectIterator
     * @param Boolean         $skip
     *
     * @return TestResult
     */
    private function testSuiteSubjects(SubjectIterator $subjectIterator, $skip = false)
    {
        return $this->suiteTester->test($subjectIterator, $skip);
    }

    /**
     * Dispatched AFTER event.
     *
     * @param ExerciseTestResult $result
     */
    private function dispatchAfterEvent(ExerciseTestResult $result)
    {
        $this->eventDispatcher->dispatch(ExerciseCompleted::AFTER, new ExerciseCompleted($result));
    }
}
