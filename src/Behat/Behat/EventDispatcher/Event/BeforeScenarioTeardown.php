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
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\BeforeTeardown;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an event before scenario teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeScenarioTeardown extends ScenarioTested implements BeforeTeardown
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
     * @param Environment $env
     * @param FeatureNode $feature
     * @param Scenario    $scenario
     * @param TestResult  $result
     */
    public function __construct(
        Environment $env,
        FeatureNode $feature,
        Scenario $scenario,
        TestResult $result
    ) {
        parent::__construct($env);

        $this->feature = $feature;
        $this->scenario = $scenario;
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
