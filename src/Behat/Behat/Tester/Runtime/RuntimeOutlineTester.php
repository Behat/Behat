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

/**
 * Tester executing outline tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeOutlineTester implements OutlineTester
{
    /**
     * @var ScenarioTester
     */
    private $scenarioTester;

    /**
     * Initializes tester.
     *
     * @param ScenarioTester $scenarioTester
     */
    public function __construct(ScenarioTester $scenarioTester)
    {
        $this->scenarioTester = $scenarioTester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, OutlineNode $outline, $skip)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, OutlineNode $outline, $skip = false)
    {
        $results = array();
        foreach ($outline->getExamples() as $example) {
            $setup = $this->scenarioTester->setUp($env, $feature, $example, $skip);
            $localSkip = !$setup->isSuccessful() || $skip;
            $testResult = $this->scenarioTester->test($env, $feature, $example, $localSkip);
            $teardown = $this->scenarioTester->tearDown($env, $feature, $example, $localSkip, $testResult);

            $integerResult = new IntegerTestResult($testResult->getResultCode());
            $results[] = new TestWithSetupResult($setup, $integerResult, $teardown);
        }

        return new TestResults($results);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, OutlineNode $outline, $skip, TestResult $result)
    {
        return new SuccessfulTeardown();
    }
}
