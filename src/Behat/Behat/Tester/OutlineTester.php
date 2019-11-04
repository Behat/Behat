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
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prepares and tests provided outline object against provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface OutlineTester
{
    /**
     * Sets up background for a test.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param OutlineNode $outline
     * @param bool     $skip
     *
     * @return Setup
     */
    public function setUp(Environment $env, FeatureNode $feature, OutlineNode $outline, $skip);

    /**
     * Tests outline.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param OutlineNode $outline
     * @param bool     $skip
     *
     * @return TestResult
     */
    public function test(Environment $env, FeatureNode $feature, OutlineNode $outline, $skip);

    /**
     * Sets up background for a test.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param OutlineNode $outline
     * @param bool     $skip
     * @param TestResult  $result
     *
     * @return Teardown
     */
    public function tearDown(Environment $env, FeatureNode $feature, OutlineNode $outline, $skip, TestResult $result);
}
