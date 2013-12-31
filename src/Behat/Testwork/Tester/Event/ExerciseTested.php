<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Event;

use Behat\Testwork\Tester\Result\ExerciseTestResult;
use Symfony\Component\EventDispatcher\Event;

/**
 * Testwork exercise tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExerciseTested extends Event
{
    const BEFORE = 'tester.exercise_tested.before';
    const AFTER = 'tester.exercise_tested.after';

    /**
     * @var null|ExerciseTestResult
     */
    private $testResult;
    /**
     * @var Boolean
     */
    private $stopped = false;

    /**
     * Initializes event.
     *
     * @param null|ExerciseTestResult $testResult
     * @param Boolean                 $stopped
     */
    public function __construct(ExerciseTestResult $testResult = null, $stopped = false)
    {
        $this->testResult = $testResult;
        $this->stopped = $stopped;
    }

    /**
     * Checks whether exercise was completed entirely.
     *
     * @return Boolean
     */
    public function isCompleted()
    {
        return !$this->stopped;
    }

    /**
     * Returns exercise test result (if tested).
     *
     * @return null|ExerciseTestResult
     */
    public function getTestResult()
    {
        return $this->testResult;
    }

    /**
     * Returns step tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->testResult->getResultCode();
    }
}
