<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prepares and tests provided exercise specifications.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Exercise
{
    /**
     * Sets up exercise for a test.
     *
     * @param SpecificationIterator[] $iterators
     * @param bool                    $skip
     *
     * @return Setup
     */
    public function setUp(array $iterators, $skip);

    /**
     * Tears down exercise after a test.
     *
     * @param SpecificationIterator[] $iterators
     * @param bool                    $skip
     *
     * @return Teardown
     */
    public function tearDown(array $iterators, $skip, TestResult $result);

    /**
     * Tests suites specifications.
     *
     * @param SpecificationIterator[] $iterators
     * @param bool                    $skip
     *
     * @return TestResult
     */
    public function test(array $iterators, $skip);
}
