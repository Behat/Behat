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
use Behat\Behat\Tester\ScenarioTester;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Result\TestWithSetupResult;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;

/**
 * Behat in-runtime scenario tester.
 *
 * Scenario tester executing scenario and outline example tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RuntimeScenarioTester implements ScenarioTester
{
    /**
     * @var StepTester
     */
    private $stepTester;
    /**
     * @var BackgroundTester
     */
    private $backgroundTester;

    /**
     * Initializes tester.
     *
     * @param StepTester       $stepTester
     * @param BackgroundTester $backgroundTester
     */
    public function __construct(StepTester $stepTester, BackgroundTester $backgroundTester)
    {
        $this->stepTester = $stepTester;
        $this->backgroundTester = $backgroundTester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, Scenario $example, $skip)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, Scenario $scenario, $skip = false)
    {
        $results = array();

        if ($feature->hasBackground()) {
            $setup = $this->backgroundTester->setUp($env, $feature, $skip);
            $skip = !$setup->isSuccessful() || $skip;

            $testResult = $this->backgroundTester->test($env, $feature, $skip);
            $skip = !$testResult->isPassed() || $skip;

            $teardown = $this->backgroundTester->tearDown($env, $feature, $skip, $testResult);
            $skip = !$teardown->isSuccessful() || $skip;

            $integerResult = new IntegerTestResult($testResult->getResultCode());
            $results[] = new TestWithSetupResult($setup, $integerResult, $teardown);
        }

        foreach ($scenario->getSteps() as $step) {
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
    public function tearDown(Environment $env, FeatureNode $feature, Scenario $scenario, $skip, TestResult $result)
    {
        return new SuccessfulTeardown();
    }
}
