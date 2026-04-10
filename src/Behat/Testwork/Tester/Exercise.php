<?php

/*
 * This file is part of the Behat Testwork.
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
 *
 * @template TSpec
 */
interface Exercise
{
    /**
     * Sets up exercise for a test.
     *
     * @param SpecificationIterator<TSpec>[] $iterators
     */
    public function setUp(array $iterators, bool $skip): Setup;

    /**
     * Tests suites specifications.
     *
     * @param SpecificationIterator<TSpec>[] $iterators
     */
    public function test(array $iterators, bool $skip): TestResult;

    /**
     * Tears down exercise after a test.
     *
     * @param SpecificationIterator<TSpec>[] $iterators
     */
    public function tearDown(array $iterators, bool $skip, TestResult $result): Teardown;
}
