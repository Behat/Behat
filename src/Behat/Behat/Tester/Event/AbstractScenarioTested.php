<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Event;

use Behat\Behat\Tester\Result\StepContainerTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Event\HookableEvent;

/**
 * Abstract scenario tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class AbstractScenarioTested extends HookableEvent
{
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var ScenarioInterface
     */
    private $scenario;
    /**
     * @var StepContainerTestResult
     */
    private $testResult;

    /**
     * Initializes event.
     *
     * @param Environment                  $environment
     * @param FeatureNode                  $feature
     * @param ScenarioInterface            $scenario
     * @param null|StepContainerTestResult $testResult
     * @param null|CallResults             $hookCallResults
     */
    public function __construct(
        Environment $environment,
        FeatureNode $feature,
        ScenarioInterface $scenario,
        StepContainerTestResult $testResult = null,
        CallResults $hookCallResults = null
    ) {
        parent::__construct($environment, $hookCallResults);

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
     * @return ScenarioInterface
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Returns scenario test result (if scenario was tested).
     *
     * @return null|StepContainerTestResult
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
