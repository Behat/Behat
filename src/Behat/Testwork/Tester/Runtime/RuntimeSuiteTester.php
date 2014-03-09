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
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;
use Behat\Testwork\Tester\SpecificationTester;
use Behat\Testwork\Tester\SuiteTester;

/**
 * Testwork in-runtime suite tester.
 *
 * Tester executing suite tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RuntimeSuiteTester implements SuiteTester
{
    /**
     * @var SpecificationTester
     */
    private $specTester;

    /**
     * Initializes tester.
     *
     * @param SpecificationTester $specTester
     */
    public function __construct(SpecificationTester $specTester)
    {
        $this->specTester = $specTester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, SpecificationIterator $iterator, $skip)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, SpecificationIterator $iterator, $skip = false)
    {
        $results = array();
        foreach ($iterator as $specification) {
            $setup = $this->specTester->setUp($env, $specification, $skip);
            $skip = $skip || !$setup->isSuccessful();

            $testResult = $this->specTester->test($env, $specification, $skip);

            $teardown = $this->specTester->tearDown($env, $specification, $skip, $testResult);
            $skip = $skip || !$teardown->isSuccessful();

            $integerResult = new IntegerTestResult($testResult->getResultCode());
            $results[] = new TestWithSetupResult($setup, $integerResult, $teardown);
        }

        return new TestResults($results);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, SpecificationIterator $iterator, $skip, TestResult $result)
    {
        return new SuccessfulTeardown();
    }
}
