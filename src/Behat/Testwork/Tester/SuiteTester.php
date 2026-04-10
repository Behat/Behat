<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prepares and tests provided suite specifications against provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template TSpec
 */
interface SuiteTester
{
    /**
     * Sets up suite for a test.
     *
     * @param SpecificationIterator<TSpec> $iterator
     */
    public function setUp(Environment $env, SpecificationIterator $iterator, bool $skip): Setup;

    /**
     * Tests provided suite specifications.
     *
     * @param SpecificationIterator<TSpec> $iterator
     */
    public function test(Environment $env, SpecificationIterator $iterator, bool $skip): TestResult;

    /**
     * Tears down suite after a test.
     *
     * @param SpecificationIterator<TSpec> $iterator
     */
    public function tearDown(Environment $env, SpecificationIterator $iterator, bool $skip, TestResult $result): Teardown;
}
