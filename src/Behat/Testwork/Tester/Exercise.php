<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Subject\GroupedSubjectIterator;
use Behat\Testwork\Subject\SubjectIterator;
use Behat\Testwork\Tester\Result\ExerciseTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;

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
     * Initializes tester.
     *
     * @param SuiteTester $suiteTester
     */
    public function __construct(SuiteTester $suiteTester)
    {
        $this->suiteTester = $suiteTester;
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
        $result = $this->runExercise($subjectIterators, $skip);

        return new TestResult($result->getResultCode());
    }

    /**
     * Tests provided suites.
     *
     * @param SubjectIterator[] $subjectIterators
     * @param Boolean           $skip
     *
     * @return ExerciseTestResult
     */
    protected function runExercise(array $subjectIterators, $skip = false)
    {
        $results = array();
        foreach (GroupedSubjectIterator::group($subjectIterators) as $subjectIterator) {
            $results[] = $this->suiteTester->test($subjectIterator, $skip);
        }

        return new ExerciseTestResult(new TestResults($results));
    }
}
