<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Runtime;

use Behat\Behat\Tester\BackgroundTester;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Result\TestWithSetupResult;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;

/**
 * Tester executing background tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeBackgroundTester implements BackgroundTester
{
    /**
     * @var StepTester
     */
    private $stepTester;

    /**
     * Initializes tester.
     *
     * @param StepTester $stepTester
     */
    public function __construct(StepTester $stepTester)
    {
        $this->stepTester = $stepTester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, $skip)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, $skip)
    {
        $background = $feature->getBackground();

        $results = array();
        foreach ($background->getSteps() as $step) {
            $setup = $this->stepTester->setUp($env, $feature, $step, $skip);
            $skip = !$setup->isSuccessful() || $skip;

            $testResult = $this->stepTester->test($env, $feature, $step, $skip);
            $skip = !$testResult->isPassed() || $skip;

            $teardown = $this->stepTester->tearDown($env, $feature, $step, $skip, $testResult);
            $skip = !$teardown->isSuccessful() || $skip;

            $integerResult = new IntegerTestResult($testResult->getResultCode());
            $results[] = new TestWithSetupResult($setup, $integerResult, $teardown);
        }

        return new TestResults($results);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, $skip, TestResult $result)
    {
        return new SuccessfulTeardown();
    }
}
