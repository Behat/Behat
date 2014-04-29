<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\BeforeTeardown;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an event right before feature is teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeFeatureTeardown extends FeatureTested implements BeforeTeardown
{
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var TestResult
     */
    private $result;

    /**
     * Initializes event.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param TestResult  $result
     */
    public function __construct(Environment $env, FeatureNode $feature, TestResult $result)
    {
        parent::__construct($env);

        $this->feature = $feature;
        $this->result = $result;
    }

    /**
     * Returns feature.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Returns current test result.
     *
     * @return TestResult
     */
    public function getTestResult()
    {
        return $this->result;
    }
}
