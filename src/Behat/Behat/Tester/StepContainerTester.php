<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Result\StepContainerTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;

/**
 * Step container tester.
 *
 * Used to test both scenarios and outline examples.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepContainerTester
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
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * Initializes tester.
     *
     * @param StepTester         $stepTester
     * @param BackgroundTester   $backgroundTester
     * @param EnvironmentManager $em
     */
    public function __construct(StepTester $stepTester, BackgroundTester $backgroundTester, EnvironmentManager $em)
    {
        $this->stepTester = $stepTester;
        $this->backgroundTester = $backgroundTester;
        $this->environmentManager = $em;
    }

    /**
     * Tests step container.
     *
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param StepContainerInterface $container
     * @param Boolean                $skip
     *
     * @return TestResult
     */
    public function test(
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $container,
        $skip = false
    ) {
        $environment = $this->environmentManager->isolateEnvironment($environment, $feature);
        $result = $this->testContainer($environment, $feature, $container, $skip);

        return new TestResult($result->getResultCode());
    }

    /**
     * Tests container node.
     *
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param StepContainerInterface $container
     * @param Boolean                $skip
     *
     * @return StepContainerTestResult
     */
    protected function testContainer(
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $container,
        $skip = false
    ) {
        $results = array();

        if ($feature->hasBackground()) {
            $backgroundResults = $this->backgroundTester->test($environment, $feature, $skip);
            $results = $backgroundResults->toArray();
            $skip = TestResult::PASSED < $backgroundResults->getResultCode() ? true : $skip;
        }

        foreach ($container->getSteps() as $step) {
            $results[] = $lastResult = $this->stepTester->test($environment, $feature, $step, $skip);
            $skip = TestResult::PASSED < $lastResult->getResultCode() ? true : $skip;
        }

        return new StepContainerTestResult(new TestResults($results));
    }
}
