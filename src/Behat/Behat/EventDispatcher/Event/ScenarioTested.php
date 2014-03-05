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
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat scenario tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioTested extends LifecycleEvent implements ScenarioLikeTested
{
    const BEFORE = 'tester.scenario_tested.before';
    const AFTER = 'tester.scenario_tested.after';

    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var ScenarioNode
     */
    private $scenario;
    /**
     * @var TestResult
     */
    private $testResult;

    /**
     * Initializes event.
     *
     * @param Environment     $environment
     * @param FeatureNode     $feature
     * @param ScenarioNode    $scenario
     * @param null|TestResult $testResult
     */
    public function __construct(
        Environment $environment,
        FeatureNode $feature,
        ScenarioNode $scenario,
        TestResult $testResult = null
    ) {
        parent::__construct($environment);

        $this->feature = $feature;
        $this->scenario = $scenario;
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
     * Returns scenario node.
     *
     * @return ScenarioNode
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Returns scenario test result (if scenario was tested).
     *
     * @return null|TestResult
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
        if (null === $this->testResult) {
            return null;
        }

        return $this->testResult->getResultCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getNode()
    {
        return $this->getScenario();
    }
}
