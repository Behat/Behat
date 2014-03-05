<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Behat\Tester\Result\StepTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;

/**
 * Behat step tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepTested extends LifecycleEvent implements GherkinNodeTested
{
    const BEFORE = 'tester.step_tested.before';
    const AFTER = 'tester.step_tested.after';

    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var StepNode
     */
    private $step;
    /**
     * @var StepTestResult
     */
    private $testResult;

    /**
     * Initializes event.
     *
     * @param Environment         $environment
     * @param FeatureNode         $feature
     * @param StepNode            $step
     * @param null|StepTestResult $testResults
     */
    public function __construct(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        StepTestResult $testResults = null
    ) {
        parent::__construct($environment);

        $this->feature = $feature;
        $this->step = $step;
        $this->testResult = $testResults;
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
     * Returns step node.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Returns step test result.
     *
     * @return null|StepTestResult
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
        return $this->getStep();
    }
}
