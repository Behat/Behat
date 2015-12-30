<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Behat\Tester\Context\ScenarioContext;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Testwork\EventDispatcher\Event\BeforeTeardown;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an event before scenario teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BeforeScenarioTeardown extends ScenarioTested implements BeforeTeardown
{
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var Scenario
     */
    private $scenario;
    /**
     * @var TestResult
     */
    private $result;

    /**
     * Initializes event
     *
     * @param ScenarioContext $context
     * @param TestResult      $result
     */
    public function __construct(ScenarioContext $context, TestResult $result)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getFeature();
        $this->scenario = $context->getScenario();
        $this->result = $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return self::BEFORE_TEARDOWN;
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
     * Returns current test result.
     *
     * @return TestResult
     */
    public function getTestResult()
    {
        return $this->result;
    }
}
