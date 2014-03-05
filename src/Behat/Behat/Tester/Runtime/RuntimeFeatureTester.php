<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Runtime;

use Behat\Behat\Tester\FeatureTester;
use Behat\Behat\Tester\OutlineTester;
use Behat\Behat\Tester\ScenarioTester;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Exception\TestworkException;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Exception;

/**
 * Behat in-runtime feature tester.
 *
 * Feature tester executing feature tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RuntimeFeatureTester implements FeatureTester
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
    private $environmentManager;

    /**
     * Initializes tester.
     *
     * @param ScenarioTester     $scenarioTester
     * @param OutlineTester      $outlineTester
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(
        ScenarioTester $scenarioTester,
        OutlineTester $outlineTester,
        EnvironmentManager $environmentManager
    ) {
        $this->scenarioTester = $scenarioTester;
        $this->outlineTester = $outlineTester;
        $this->environmentManager = $environmentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $environment, $specification, $skip)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $environment, $feature, $skip = false)
    {
        $results = array();
        foreach ($feature->getScenarios() as $scenario) {
            $isolatedEnvironment = $this->environmentManager->isolateEnvironment($environment, $scenario);
            $tester = $scenario instanceof OutlineNode ? $this->outlineTester : $this->scenarioTester;

            try {
                $tester->setUp($isolatedEnvironment, $feature, $scenario, $skip);
            } catch (TestworkException $e) {
                throw $e;
            } catch (Exception $e) {
                $skip = true;
            }

            $testResult = $tester->test($isolatedEnvironment, $feature, $scenario, $skip);

            try {
                $tester->tearDown($isolatedEnvironment, $feature, $scenario, $skip, $testResult);
            } catch (TestworkException $e) {
                throw $e;
            } catch (Exception $e) {
                $skip = true;
            }

            $results[] = new TestResult($testResult->getResultCode());
        }

        return new TestResults($results);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $environment, $specification, $skip, TestResult $result)
    {
    }
}
