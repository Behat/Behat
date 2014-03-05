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
use Behat\Testwork\Tester\Result\TestResult;
use Exception;

/**
 * Behat scenario tester interface.
 *
 * This interface defines an API for Tree Scenario testers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ScenarioTester
{
    /**
     * Sets up example for a test.
     *
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param StepContainerInterface $scenario
     * @param Boolean                $skip
     *
     * @throws Exception If something goes wrong. That will cause test to be skipped.
     */
    public function setUp(Environment $environment, FeatureNode $feature, StepContainerInterface $scenario, $skip);

    /**
     * Tests example.
     *
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param StepContainerInterface $scenario
     * @param Boolean                $skip
     *
     * @return TestResult
     */
    public function test(Environment $environment, FeatureNode $feature, StepContainerInterface $scenario, $skip);

    /**
     * Tears down example after a test.
     *
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param StepContainerInterface $example
     * @param Boolean                $skip
     * @param TestResult             $result
     *
     * @throws Exception If something goes wrong. That will cause all consequent tests to be skipped.
     */
    public function tearDown(
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $example,
        $skip,
        TestResult $result
    );
}
