<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Ordering;

use Behat\Testwork\Ordering\Orderer\NoopOrderer;
use Behat\Testwork\Ordering\Orderer\Orderer;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Exercise that is ordered according to a specified algorithm.
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 *
 * @template TSpec
 *
 * @implements Exercise<TSpec>
 */
final class OrderedExercise implements Exercise
{
    /**
     * @var Orderer
     */
    private $orderer;

    /**
     * @var SpecificationIterator<TSpec>[]|null
     */
    private $unordered;

    /**
     * @var SpecificationIterator<TSpec>[]|null
     */
    private $ordered;

    /**
     * @param Exercise<TSpec> $decoratedExercise
     */
    public function __construct(
        private readonly Exercise $decoratedExercise,
    ) {
        $this->orderer = new NoopOrderer();
    }

    public function setUp(array $iterators, $skip)
    {
        return $this->decoratedExercise->setUp($this->order($iterators), $skip);
    }

    public function test(array $iterators, $skip)
    {
        return $this->decoratedExercise->test($this->order($iterators), $skip);
    }

    public function tearDown(array $iterators, $skip, TestResult $result)
    {
        return $this->decoratedExercise->tearDown($this->order($iterators), $skip, $result);
    }

    /**
     * Replace the algorithm being used for prioritisation.
     */
    public function setOrderer(Orderer $orderer): void
    {
        $this->orderer = $orderer;
    }

    /**
     * @param SpecificationIterator<TSpec>[] $iterators
     *
     * @return SpecificationIterator<TSpec>[]
     */
    private function order(array $iterators)
    {
        if (!$this->ordered || $this->unordered != $iterators) {
            $this->unordered = $iterators;
            $this->ordered = $this->orderer->order($iterators);
        }

        return $this->ordered;
    }
}
