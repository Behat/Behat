<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Specification\SpecificationIterator;
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
     * Initializes event.
     *
     * @param SpecificationIterator<mixed>[] $specificationIterators
     */
    public function __construct(
        private readonly array $specificationIterators,
        private readonly TestResult $result,
        private readonly Teardown $teardown,
    ) {
    }

    /**
     * @return SpecificationIterator[]
     */
    public function getSpecificationIterators(): array
    {
        return $this->specificationIterators;
    }

    /**
     * Returns exercise test result.
     */
    public function getTestResult(): TestResult
    {
        return $this->result;
    }

    /**
     * Returns exercise teardown result.
     */
    public function getTeardown(): Teardown
    {
        return $this->teardown;
    }
}
