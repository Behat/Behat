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
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Exercise that is ordered according to a specified algorithm
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class OrderedExercise implements Exercise
{
    /**
     * @var Orderer
     */
    private $orderer;

    /**
     * @var SpecificationIterator[]
     */
    private $unordered;

    /**
     * @var SpecificationIterator[]
     */
    private $ordered;

    /**
     * @var Exercise
     */
    private $decoratedExercise;

    /**
     * @param Exercise $decoratedExercise
     */
    public function __construct(Exercise $decoratedExercise)
    {
        $this->orderer = new NoopOrderer();
        $this->decoratedExercise = $decoratedExercise;
    }

    /**
     * Sets up exercise for a test.
     *
     * @param SpecificationIterator[] $iterators
     * @param bool $skip
     *
     * @return Setup
     */
    public function setUp(array $iterators, $skip)
    {
        return $this->decoratedExercise->setUp($this->order($iterators), $skip);
    }

    /**
     * Tests suites specifications.
     *
     * @param SpecificationIterator[] $iterators
     * @param bool $skip
     *
     * @return TestResult
     */
    public function test(array $iterators, $skip)
    {
        return $this->decoratedExercise->test($this->order($iterators), $skip);
    }

    /**
     * Tears down exercise after a test.
     *
     * @param SpecificationIterator[] $iterators
     * @param bool $skip
     * @param TestResult $result
     *
     * @return Teardown
     */
    public function tearDown(array $iterators, $skip, TestResult $result)
    {
        return $this->decoratedExercise->tearDown($this->order($iterators), $skip, $result);
    }

    /**
     * Replace the algorithm being used for prioritisation
     *
     * @param Orderer $orderer
     */
    public function setOrderer(Orderer $orderer)
    {
        $this->orderer = $orderer;
    }

    /**
     * @param SpecificationIterator[] $iterators
     * @return SpecificationIterator[]
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
