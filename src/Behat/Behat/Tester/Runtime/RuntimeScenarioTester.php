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
use Behat\Behat\Tester\StepContainerTester;
use Behat\Behat\Tester\ScenarioTester;
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
 * Tester executing scenario or example tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeScenarioTester implements ScenarioTester
{
    /**
     * @var StepContainerTester
     */
    private $containerTester;
    /**
     * @var BackgroundTester
     */
    private $backgroundTester;

    /**
     * Initializes tester.
     *
     * @param StepContainerTester $containerTester
     * @param BackgroundTester           $backgroundTester
     */
    public function __construct(StepContainerTester $containerTester, BackgroundTester $backgroundTester)
    {
        $this->containerTester = $containerTester;
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

        $results = array_merge($results, $this->containerTester->test($env, $feature, $scenario, $skip));

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
