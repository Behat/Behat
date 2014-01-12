<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Event;

use Behat\Behat\Tester\Result\FeatureTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Event\HookableEvent;

/**
 * Feature tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureTested extends HookableEvent
{
    const BEFORE = 'tester.feature_tested.before';
    const AFTER = 'tester.feature_tested.after';

    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var null|FeatureTestResult
     */
    private $testResult;

    /**
     * Initializes event.
     *
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param null|FeatureTestResult $testResult
     * @param null|CallResults       $hookCallResults
     */
    public function __construct(
        Environment $environment,
        FeatureNode $feature,
        FeatureTestResult $testResult = null,
        CallResults $hookCallResults = null
    ) {
        parent::__construct($environment, $hookCallResults);

        $this->feature = $feature;
        $this->testResult = $testResult;
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
     * Returns feature test results (if tested).
     *
     * @return null|FeatureTestResult
     */
    public function getTestResult()
    {
        return $this->testResult;
    }

    /**
     * Returns step tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->testResult->getResultCode();
    }
}
