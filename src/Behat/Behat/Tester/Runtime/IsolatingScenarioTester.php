<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Runtime;

use Behat\Behat\Tester\ScenarioTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestWithSetupResult;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;

/**
 * Scenario tester that isolates the environment for each scenario.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class IsolatingScenarioTester implements ScenarioTester
{
    /**
     * @var ScenarioTester
     */
    private $decoratedTester;
    /**
     * @var EnvironmentManager
     */
    private $envManager;

    /**
     * Initialises tester.
     *
     * @param ScenarioTester     $decoratedTester
     * @param EnvironmentManager $envManager
     */
    public function __construct(ScenarioTester $decoratedTester, EnvironmentManager $envManager)
    {
        $this->decoratedTester = $decoratedTester;
        $this->envManager = $envManager;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, Scenario $scenario, $skip)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, Scenario $scenario, $skip)
    {
        $isolatedEnvironment = $this->envManager->isolateEnvironment($env, $scenario);

        $setup = $this->decoratedTester->setUp($isolatedEnvironment, $feature, $scenario, $skip);
        $localSkip = !$setup->isSuccessful() || $skip;
        $testResult = $this->decoratedTester->test($isolatedEnvironment, $feature, $scenario, $localSkip);
        $teardown = $this->decoratedTester->tearDown($isolatedEnvironment, $feature, $scenario, $localSkip, $testResult);

        $integerResult = new IntegerTestResult($testResult->getResultCode());

        return new TestWithSetupResult($setup, $integerResult, $teardown);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, Scenario $scenario, $skip, TestResult $result)
    {
        return new SuccessfulTeardown();
    }
}
