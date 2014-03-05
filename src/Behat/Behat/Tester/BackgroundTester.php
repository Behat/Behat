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
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResults;
use Exception;

/**
 * Behat background tester interface.
 *
 * This interface defines an API for Tree Background testers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface BackgroundTester
{
    /**
     * Sets up background for a test.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @throws Exception If something goes wrong. That will cause test to be skipped.
     */
    public function setUp(Environment $environment, FeatureNode $feature, $skip);

    /**
     * Tests background.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @return TestResults
     */
    public function test(Environment $environment, FeatureNode $feature, $skip);

    /**
     * Tears down background after a test.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     * @param TestResults $results
     *
     * @throws Exception If something goes wrong. That will cause all consequent tests to be skipped.
     */
    public function tearDown(Environment $environment, FeatureNode $feature, $skip, TestResults $results);
}
