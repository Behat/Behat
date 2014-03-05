<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Result\StepTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Exception;

/**
 * Behat step tester interface.
 *
 * This interface defines an API for Tree Step testers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface StepTester
{
    /**
     * Sets up step for a test.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param Boolean     $skip
     *
     * @throws Exception If something goes wrong. That will cause test to be skipped.
     */
    public function setUp(Environment $environment, FeatureNode $feature, StepNode $step, $skip);

    /**
     * Tests step.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param Boolean     $skip
     *
     * @return StepTestResult
     */
    public function test(Environment $environment, FeatureNode $feature, StepNode $step, $skip);

    /**
     * Tears down step after a test.
     *
     * @param Environment    $environment
     * @param FeatureNode    $feature
     * @param StepNode       $step
     * @param Boolean        $skip
     * @param StepTestResult $result
     *
     * @throws Exception If something goes wrong. That will cause all consequent tests to be skipped.
     */
    public function tearDown(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        $skip,
        StepTestResult $result
    );
}
