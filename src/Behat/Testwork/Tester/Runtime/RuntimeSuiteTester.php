<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Runtime;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Result\TestWithSetupResult;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;
use Behat\Testwork\Tester\Setup\Teardown;
use Behat\Testwork\Tester\SpecificationTester;
use Behat\Testwork\Tester\SuiteTester;

/**
 * Tester executing suite tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template TSpec
 *
 * @implements SuiteTester<TSpec>
 */
final class RuntimeSuiteTester implements SuiteTester
{
    /**
     * Initializes tester.
     *
     * @param SpecificationTester<TSpec> $specTester
     */
    public function __construct(
        private readonly SpecificationTester $specTester,
    ) {
    }

    public function setUp(Environment $env, SpecificationIterator $iterator, $skip): Setup
    {
        return new SuccessfulSetup();
    }

    public function test(Environment $env, SpecificationIterator $iterator, $skip = false): TestResult
    {
        $results = [];
        foreach ($iterator as $specification) {
            $setup = $this->specTester->setUp($env, $specification, $skip);
            $localSkip = !$setup->isSuccessful() || $skip;
            $testResult = $this->specTester->test($env, $specification, $localSkip);
            $teardown = $this->specTester->tearDown($env, $specification, $localSkip, $testResult);

            $integerResult = new IntegerTestResult($testResult->getResultCode());
            $results[] = new TestWithSetupResult($setup, $integerResult, $teardown);
        }

        return new TestResults($results);
    }

    public function tearDown(Environment $env, SpecificationIterator $iterator, $skip, TestResult $result): Teardown
    {
        return new SuccessfulTeardown();
    }
}
