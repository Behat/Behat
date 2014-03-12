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
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prepares and tests provided scenario object against provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ScenarioTester
{
    /**
     * Sets up example for a test.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param Scenario    $scenario
     * @param Boolean     $skip
     *
     * @return Setup
     */
    public function setUp(Environment $env, FeatureNode $feature, Scenario $scenario, $skip);

    /**
     * Tests example.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param Scenario    $scenario
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    public function test(Environment $env, FeatureNode $feature, Scenario $scenario, $skip);

    /**
     * Tears down example after a test.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param Scenario    $scenario
     * @param Boolean     $skip
     * @param TestResult  $result
     *
     * @return Teardown
     */
    public function tearDown(Environment $env, FeatureNode $feature, Scenario $scenario, $skip, TestResult $result);
}
