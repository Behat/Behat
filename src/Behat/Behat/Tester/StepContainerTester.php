<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestWithSetupResult;

/**
 * Tests provided collection of steps against provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StepContainerTester
{
    /**
     * Initializes tester.
     */
    public function __construct(
        private readonly StepTester $stepTester,
    ) {
    }

    /**
     * Tests container.
     *
     * @param bool                $skip
     *
     * @return list<TestResult>
     */
    public function test(Environment $env, FeatureNode $feature, StepContainerInterface $container, $skip): array
    {
        $results = [];
        foreach ($container->getSteps() as $step) {
            $setup = $this->stepTester->setUp($env, $feature, $step, $skip);
            $skipSetup = !$setup->isSuccessful() || $skip;

            $testResult = $this->stepTester->test($env, $feature, $step, $skipSetup);
            $skip = !$testResult->isPassed() || $skip;

            $teardown = $this->stepTester->tearDown($env, $feature, $step, $skipSetup, $testResult);
            $skip = $skip || $skipSetup || !$teardown->isSuccessful();

            $integerResult = new IntegerTestResult($testResult->getResultCode());
            $results[] = new TestWithSetupResult($setup, $integerResult, $teardown);
        }

        return $results;
    }
}
