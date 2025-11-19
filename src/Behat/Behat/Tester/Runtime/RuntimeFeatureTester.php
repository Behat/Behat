<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Runtime;

use Behat\Behat\Tester\OutlineTester;
use Behat\Behat\Tester\ScenarioTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Result\TestWithSetupResult;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;
use Behat\Testwork\Tester\SpecificationTester;

/**
 * Tester executing feature tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements SpecificationTester<FeatureNode>
 */
final class RuntimeFeatureTester implements SpecificationTester
{
    public function __construct(
        private readonly ScenarioTester $scenarioTester,
        private readonly OutlineTester $outlineTester,
    ) {
    }

    public function setUp(Environment $env, $spec, $skip)
    {
        return new SuccessfulSetup();
    }

    public function test(Environment $env, $spec, $skip = false)
    {
        $results = [];
        foreach ($spec->getScenarios() as $scenario) {
            $tester = $scenario instanceof OutlineNode ? $this->outlineTester : $this->scenarioTester;

            $setup = $tester->setUp($env, $spec, $scenario, $skip);
            $localSkip = !$setup->isSuccessful() || $skip;
            $testResult = $tester->test($env, $spec, $scenario, $localSkip);
            $teardown = $tester->tearDown($env, $spec, $scenario, $localSkip, $testResult);

            $integerResult = new IntegerTestResult($testResult->getResultCode());
            $results[] = new TestWithSetupResult($setup, $integerResult, $teardown);
        }

        return new TestResults($results);
    }

    public function tearDown(Environment $env, $spec, $skip, TestResult $result)
    {
        return new SuccessfulTeardown();
    }
}
