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
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;

/**
 * Tester executing background tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeBackgroundTester implements BackgroundTester
{
    /**
     * @var StepContainerTester
     */
    private $containerTester;

    /**
     * Initializes tester.
     *
     * @param StepContainerTester $containerTester
     */
    public function __construct(StepContainerTester $containerTester)
    {
        $this->containerTester = $containerTester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, $skip)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, $skip)
    {
        $results = $this->containerTester->test($env, $feature, $feature->getBackground(), $skip);

        return new TestResults($results);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, $skip, TestResult $result)
    {
        return new SuccessfulTeardown();
    }
}
