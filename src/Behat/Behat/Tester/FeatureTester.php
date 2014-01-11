<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Result\FeatureTestResult;
use Behat\Behat\Tester\Result\OutlineTestResult;
use Behat\Behat\Tester\Result\StepContainerTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\SubjectTester;

/**
 * Feature tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureTester implements SubjectTester
{
    /**
     * @var StepContainerTester
     */
    private $scenarioTester;
    /**
     * @var OutlineTester
     */
    private $outlineTester;

    /**
     * Initializes tester.
     *
     * @param StepContainerTester $scenarioTester
     * @param OutlineTester       $outlineTester
     */
    public function __construct(StepContainerTester $scenarioTester, OutlineTester $outlineTester)
    {
        $this->scenarioTester = $scenarioTester;
        $this->outlineTester = $outlineTester;
    }

    /**
     * Tests feature.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    public function test(Suite $suite, Environment $environment, $feature, $skip = false)
    {
        $result = $this->testFeature($suite, $environment, $feature, $skip);

        return new TestResult($result->getResultCode());
    }

    /**
     * Tests feature scenarios.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @return FeatureTestResult
     */
    protected function testFeature(Suite $suite, Environment $environment, FeatureNode $feature, $skip = false)
    {
        $results = array();
        foreach ($feature->getScenarios() as $scenario) {
            $results[] = $this->testScenario($suite, $environment, $feature, $scenario, $skip);
        }

        return new FeatureTestResult(new TestResults($results));
    }

    /**
     * Tests any scenario.
     *
     * @param Suite             $suite
     * @param Environment       $environment
     * @param FeatureNode       $feature
     * @param ScenarioInterface $scenario
     * @param Boolean           $skip
     *
     * @return TestResult
     */
    private function testScenario(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        ScenarioInterface $scenario,
        $skip = false
    ) {
        if ($scenario instanceof OutlineNode) {
            return $this->testOutlineNode($suite, $environment, $feature, $scenario, $skip);
        }

        return $this->testScenarioNode($suite, $environment, $feature, $scenario, $skip);
    }

    /**
     * Tests scenario outline node.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param OutlineNode $scenario
     * @param Boolean     $skip
     *
     * @return OutlineTestResult
     */
    private function testOutlineNode(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $scenario,
        $skip = false
    ) {
        return $this->outlineTester->test($suite, $environment, $feature, $scenario, $skip);
    }

    /**
     * Tests scenario node.
     *
     * @param Suite        $suite
     * @param Environment  $environment
     * @param FeatureNode  $feature
     * @param ScenarioNode $scenario
     * @param Boolean      $skip
     *
     * @return StepContainerTestResult
     */
    private function testScenarioNode(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        ScenarioNode $scenario,
        $skip = false
    ) {
        return $this->scenarioTester->test($suite, $environment, $feature, $scenario, $skip);
    }
}
