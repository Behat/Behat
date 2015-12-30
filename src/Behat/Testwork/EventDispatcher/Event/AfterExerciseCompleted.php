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
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents an event in which exercise was completed.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterExerciseCompleted extends ExerciseCompleted implements AfterTested
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
     * @var Teardown
     */
    private $teardown;

    /**
     * Initializes event.
     *
     * @param ExerciseContext $context
     * @param TestResult      $result
     * @param Teardown        $teardown
     */
    public function __construct(ExerciseContext $context, TestResult $result, Teardown $teardown)
    {
        $this->specificationIterators = $context->getGroupedSpecificationIterators();
        $this->result = $result;
        $this->teardown = $teardown;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return self::AFTER;
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

    /**
     * Returns exercise teardown result.
     *
     * @return Teardown
     */
    public function getTeardown()
    {
        return $this->teardown;
    }
}
