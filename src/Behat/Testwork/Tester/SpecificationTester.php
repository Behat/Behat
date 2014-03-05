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
use Exception;

/**
 * Testwork specification tester interface.
 *
 * This interface defines an API for Testwork specification testers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SpecificationTester
{
    /**
     * Sets up specification for a test.
     *
     * @param Environment $environment
     * @param mixed       $specification
     * @param Boolean     $skip
     *
     * @throws Exception If something goes wrong. That will cause test to be skipped.
     */
    public function setUp(Environment $environment, $specification, $skip);

    /**
     * Tests provided specification.
     *
     * @param Environment $environment
     * @param mixed       $specification
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    public function test(Environment $environment, $specification, $skip);

    /**
     * Tears down specification after a test.
     *
     * @param Environment $environment
     * @param mixed       $specification
     * @param Boolean     $skip
     * @param TestResult  $result
     *
     * @throws Exception If something goes wrong. That will cause all consequent tests to be skipped.
     */
    public function tearDown(Environment $environment, $specification, $skip, TestResult $result);
}
