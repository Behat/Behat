<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Tester\Context\ExerciseContext;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an event right before exercise teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeExerciseTeardown extends ExerciseCompleted implements BeforeTeardown
{
    /**
     * @var GroupedSpecificationIterator[]
     */
    private $specificationIterators;
    /**
     * @var TestResult
     */
    private $result;

    /**
     * Initializes event.
     *
     * @param ExerciseContext $context
     * @param TestResult      $result
     */
    public function __construct(ExerciseContext $context, TestResult $result)
    {
        $this->specificationIterators = $context->getGroupedSpecificationIterators();
        $this->result = $result;
    }

    /**
     * Returns specification iterators.
     *
     * @return GroupedSpecificationIterator[]
     */
    public function getSpecificationIterators()
    {
        return $this->specificationIterators;
    }

    /**
     * Returns exercise test result.
     *
     * @return TestResult
     */
    public function getTestResult()
    {
        return $this->result;
    }
}
