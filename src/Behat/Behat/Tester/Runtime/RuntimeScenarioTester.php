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
use Behat\Behat\Tester\StepContainerTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Result\TestWithSetupResult;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Tester executing scenario or example tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeScenarioTester implements ScenarioTester
{
    /**
     * Initializes tester.
     */
    public function __construct(
        private readonly StepContainerTester $containerTester,
        private readonly BackgroundTester $backgroundTester,
    ) {
    }

    public function setUp(Environment $env, FeatureNode $feature, Scenario $scenario, $skip): Setup
    {
        return new SuccessfulSetup();
    }

    public function test(Environment $env, FeatureNode $feature, Scenario $scenario, $skip = false): TestResult
    {
        $results = [];

        if ($feature->hasBackground()) {
            $backgroundResult = $this->testBackground($env, $feature, $skip);
            $skip = !$backgroundResult->isPassed() || $skip;

            $results[] = $backgroundResult;
        }

        $results = array_merge($results, $this->containerTester->test($env, $feature, $scenario, $skip));

        return new TestResults($results);
    }

    public function tearDown(Environment $env, FeatureNode $feature, Scenario $scenario, $skip, TestResult $result): Teardown
    {
        return new SuccessfulTeardown();
    }

    /**
     * Tests background of the provided feature against provided environment.
     *
     * @param bool     $skip
     */
    private function testBackground(Environment $env, FeatureNode $feature, $skip): TestWithSetupResult
    {
        $setup = $this->backgroundTester->setUp($env, $feature, $skip);
        $skipSetup = !$setup->isSuccessful() || $skip;
        $testResult = $this->backgroundTester->test($env, $feature, $skipSetup);
        $teardown = $this->backgroundTester->tearDown($env, $feature, $skipSetup, $testResult);

        $integerResult = new IntegerTestResult($testResult->getResultCode());

        return new TestWithSetupResult($setup, $integerResult, $teardown);
    }
}
