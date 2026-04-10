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
     */
    public function setUp(Environment $env, FeatureNode $feature, Scenario $scenario, bool $skip): Setup;

    /**
     * Tests example.
     */
    public function test(Environment $env, FeatureNode $feature, Scenario $scenario, bool $skip): TestResult;

    /**
     * Tears down example after a test.
     */
    public function tearDown(Environment $env, FeatureNode $feature, Scenario $scenario, bool $skip, TestResult $result): Teardown;
}
