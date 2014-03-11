<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Runtime;

use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Result\TestWithSetupResult;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;
use Behat\Testwork\Tester\SuiteTester;

/**
 * Tester executing exercises in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeExercise implements Exercise
{
    /**
     * @var EnvironmentManager
     */
    private $envManager;
    /**
     * @var SuiteTester
     */
    private $suiteTester;

    /**
     * Initializes tester.
     *
     * @param EnvironmentManager $envManager
     * @param SuiteTester        $suiteTester
     */
    public function __construct(EnvironmentManager $envManager, SuiteTester $suiteTester)
    {
        $this->envManager = $envManager;
        $this->suiteTester = $suiteTester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(array $iterators, $skip)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(array $iterators, $skip = false)
    {
        $results = array();
        foreach (GroupedSpecificationIterator::group($iterators) as $iterator) {
            $environment = $this->envManager->buildEnvironment($iterator->getSuite());

            $setup = $this->suiteTester->setUp($environment, $iterator, $skip);
            $localSkip = !$setup->isSuccessful() || $skip;
            $testResult = $this->suiteTester->test($environment, $iterator, $localSkip);
            $teardown = $this->suiteTester->tearDown($environment, $iterator, $localSkip, $testResult);

            $integerResult = new IntegerTestResult($testResult->getResultCode());
            $results[] = new TestWithSetupResult($setup, $integerResult, $teardown);
        }

        return new TestResults($results);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(array $iterators, $skip, TestResult $result)
    {
        return new SuccessfulTeardown();
    }
}
