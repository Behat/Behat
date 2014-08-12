<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Priority;

use Behat\Behat\Tester\Priority\Prioritiser\NullPrioritiser;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Exercise as BaseExercise;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Exercise that is prioritised according to a specified algorithm
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
class Exercise implements BaseExercise
{
    /**
     * @var Prioritiser
     */
    private $prioritiser;

    /**
     * @var BaseExercise
     */
    private $decoratedExercise;

    /**
     * @param BaseExercise $decoratedExercise
     */
    function __construct(BaseExercise $decoratedExercise)
    {
        $this->prioritiser = new NullPrioritiser();
        $this->decoratedExercise = $decoratedExercise;
    }

    /**
     * Sets up exercise for a test.
     *
     * @param SpecificationIterator[] $iterators
     * @param Boolean $skip
     *
     * @return Setup
     */
    public function setUp(array $iterators, $skip)
    {
        return $this->decoratedExercise->setUp($this->prioritiser->prioritise($iterators), $skip);
    }

    /**
     * Tests suites specifications.
     *
     * @param SpecificationIterator[] $iterators
     * @param Boolean $skip
     *
     * @return TestResult
     */
    public function test(array $iterators, $skip)
    {
        return $this->decoratedExercise->test($this->prioritiser->prioritise($iterators), $skip);
    }

    /**
     * Tears down exercise after a test.
     *
     * @param SpecificationIterator[] $iterators
     * @param Boolean $skip
     * @param TestResult $result
     *
     * @return Teardown
     */
    public function tearDown(array $iterators, $skip, TestResult $result)
    {
        return $this->decoratedExercise->tearDown($this->prioritiser->prioritise($iterators), $skip, $result);
    }

    /**
     * Replace the algorithm being used for prioritisation
     *
     * @param Prioritiser $prioritiser
     */
    public function setPrioritiser(Prioritiser $prioritiser)
    {
        $this->prioritiser = $prioritiser;
    }
}
