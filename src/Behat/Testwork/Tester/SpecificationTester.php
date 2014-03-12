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
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prepares and tests provided specification against provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SpecificationTester
{
    /**
     * Sets up specification for a test.
     *
     * @param Environment $env
     * @param mixed       $spec
     * @param Boolean     $skip
     *
     * @return Setup
     */
    public function setUp(Environment $env, $spec, $skip);

    /**
     * Tests provided specification.
     *
     * @param Environment $env
     * @param mixed       $spec
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    public function test(Environment $env, $spec, $skip);

    /**
     * Tears down specification after a test.
     *
     * @param Environment $env
     * @param mixed       $spec
     * @param Boolean     $skip
     * @param TestResult  $result
     *
     * @return Teardown
     */
    public function tearDown(Environment $env, $spec, $skip, TestResult $result);
}
