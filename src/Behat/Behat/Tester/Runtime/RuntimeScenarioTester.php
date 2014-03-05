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
use Behat\Behat\Tester\ExampleTester;
use Behat\Behat\Tester\ScenarioTester;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Exception\TestworkException;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Exception;

/**
 * Behat in-runtime scenario tester.
 *
 * Scenario tester executing scenario and outline example tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RuntimeScenarioTester implements ScenarioTester, ExampleTester
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
    public function setUp(Environment $environment, FeatureNode $feature, StepContainerInterface $example, $skip)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function test(
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $scenario,
        $skip = false
    ) {
        $results = array();

        if ($feature->hasBackground()) {
            try {
                $this->backgroundTester->setUp($environment, $feature, $skip);
            } catch (Exception $e) {
                $skip = true;
            }

            $testResult = $this->backgroundTester->test($environment, $feature, $skip);

            try {
                $this->backgroundTester->tearDown($environment, $feature, $skip, $testResult);
            } catch (TestworkException $e) {
                throw $e;
            } catch (Exception $e) {
                $skip = true;
            }

            $results = $testResult->toArray();
        }

        foreach ($scenario->getSteps() as $step) {
            try {
                $this->stepTester->setUp($environment, $feature, $step, $skip);
            } catch (Exception $e) {
                $skip = true;
            }

            $testResult = $this->stepTester->test($environment, $feature, $step, $skip);

            try {
                $this->stepTester->tearDown($environment, $feature, $step, $skip, $testResult);
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
    public function tearDown(
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $example,
        $skip,
        TestResult $result
    ) {
    }
}
