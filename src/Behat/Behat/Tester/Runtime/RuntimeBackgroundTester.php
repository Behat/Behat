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
use Behat\Behat\Tester\Exception\FeatureHasNoBackgroundException;
use Behat\Behat\Tester\StepContainerTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Tester executing background tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeBackgroundTester implements BackgroundTester
{
    /**
     * Initializes tester.
     */
    public function __construct(
        private readonly StepContainerTester $containerTester,
    ) {
    }

    public function setUp(Environment $env, FeatureNode $feature, $skip): Setup
    {
        return new SuccessfulSetup();
    }

    public function test(Environment $env, FeatureNode $feature, $skip): TestResult
    {
        $background = $feature->getBackground();

        if (null === $background) {
            throw new FeatureHasNoBackgroundException(sprintf(
                'Feature `%s` has no background that could be tested.',
                $feature->getFile()
            ), $feature);
        }

        if (!$background->hasSteps()) {
            return new IntegerTestResult(TestResult::PASSED);
        }

        $results = $this->containerTester->test($env, $feature, $background, $skip);

        return new TestResults($results);
    }

    public function tearDown(Environment $env, FeatureNode $feature, $skip, TestResult $result): Teardown
    {
        return new SuccessfulTeardown();
    }
}
