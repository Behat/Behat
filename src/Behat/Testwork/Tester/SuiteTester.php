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
use Exception;

/**
 * Testwork suite tester interface.
 *
 * This interface defines an API for Testwork suite testers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SuiteTester
{
    /**
     * Sets up suite for a test.
     *
     * @param Environment           $environment
     * @param SpecificationIterator $iterator
     * @param Boolean               $skip
     *
     * @throws Exception If something goes wrong. That will cause test to be skipped.
     */
    public function setUp(Environment $environment, SpecificationIterator $iterator, $skip);

    /**
     * Tests provided suite specifications.
     *
     * @param Environment           $environment
     * @param SpecificationIterator $iterator
     * @param Boolean               $skip
     *
     * @return TestResult
     */
    public function test(Environment $environment, SpecificationIterator $iterator, $skip);

    /**
     * Tears down suite after a test.
     *
     * @param Environment           $environment
     * @param SpecificationIterator $iterator
     * @param Boolean               $skip
     * @param TestResult            $result
     *
     * @throws Exception If something goes wrong. That will cause all consequent tests to be skipped.
     */
    public function tearDown(Environment $environment, SpecificationIterator $iterator, $skip, TestResult $result);
}
