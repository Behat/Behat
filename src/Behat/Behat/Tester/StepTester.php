<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prepares and tests provided step object against provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface StepTester
{
    /**
     * Sets up step for a test.
     */
    public function setUp(Environment $env, FeatureNode $feature, StepNode $step, bool $skip): Setup;

    /**
     * Tests step.
     */
    public function test(Environment $env, FeatureNode $feature, StepNode $step, bool $skip): StepResult;

    /**
     * Tears down step after a test.
     */
    public function tearDown(Environment $env, FeatureNode $feature, StepNode $step, bool $skip, StepResult $result): Teardown;
}
