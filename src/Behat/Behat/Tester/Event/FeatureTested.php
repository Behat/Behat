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
use Behat\Testwork\Hook\Event\LifecycleEvent;
use Behat\Testwork\Suite\Suite;

/**
 * Feature tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureTested extends LifecycleEvent
{
    const BEFORE = 'tester.feature_tested.before';
    const AFTER = 'tester.feature_tested.after';

    /**
     * @var null|FeatureTestResult
     */
    private $testResult;
    /**
     * @var null|CallResults
     */
    private $hookCallResults;

    /**
     * Initializes event.
     *
     * @param Suite                  $suite
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param null|FeatureTestResult $testResult
     * @param null|CallResults       $hookCallResults
     */
    public function __construct(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        FeatureTestResult $testResult = null,
        CallResults $hookCallResults = null
    ) {
        parent::__construct($suite, $environment, $feature);

        $this->testResult = $testResult;
        $this->hookCallResults = $hookCallResults;
    }

    /**
     * Returns feature.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->getSubject();
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
     * Returns hook call results.
     *
     * @return null|CallResults
     */
    public function getHookCallResults()
    {
        return $this->hookCallResults;
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
