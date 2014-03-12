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
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;
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
 */
final class RuntimeFeatureTester implements SpecificationTester
{
    /**
     * @var ScenarioTester
     */
    private $scenarioTester;
    /**
     * @var OutlineTester
     */
    private $outlineTester;
    /**
     * @var EnvironmentManager
     */
    private $envManager;

    /**
     * Initializes tester.
     *
     * @param ScenarioTester     $scenarioTester
     * @param OutlineTester      $outlineTester
     * @param EnvironmentManager $envManager
     */
    public function __construct(
        ScenarioTester $scenarioTester,
        OutlineTester $outlineTester,
        EnvironmentManager $envManager
    ) {
        $this->scenarioTester = $scenarioTester;
        $this->outlineTester = $outlineTester;
        $this->envManager = $envManager;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, $spec, $skip)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, $feature, $skip = false)
    {
        $results = array();
        foreach ($feature->getScenarios() as $scenario) {
            $isolatedEnvironment = $this->envManager->isolateEnvironment($env, $scenario);
            $tester = $scenario instanceof OutlineNode ? $this->outlineTester : $this->scenarioTester;

            $setup = $tester->setUp($isolatedEnvironment, $feature, $scenario, $skip);
            $localSkip = !$setup->isSuccessful() || $skip;
            $testResult = $tester->test($isolatedEnvironment, $feature, $scenario, $localSkip);
            $teardown = $tester->tearDown($isolatedEnvironment, $feature, $scenario, $localSkip, $testResult);

            $integerResult = new IntegerTestResult($testResult->getResultCode());
            $results[] = new TestWithSetupResult($setup, $integerResult, $teardown);
        }

        return new TestResults($results);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, $spec, $skip, TestResult $result)
    {
        return new SuccessfulTeardown();
    }
}
