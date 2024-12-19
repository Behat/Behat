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
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents an event after scenario has been tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterScenarioTested extends ScenarioTested implements AfterTested
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
     * @var Teardown
     */
    private $teardown;

    /**
     * Initializes event
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param Scenario    $scenario
     * @param TestResult  $result
     * @param Teardown    $teardown
     */
    public function __construct(
        Environment $env,
        FeatureNode $feature,
        Scenario $scenario,
        TestResult $result,
        Teardown $teardown
    ) {
        parent::__construct($env);

        $this->feature = $feature;
        $this->scenario = $scenario;
        $this->result = $result;
        $this->teardown = $teardown;
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
     * @return Scenario
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

    /**
     * Returns current test teardown.
     *
     * @return Teardown
     */
    public function getTeardown()
    {
        return $this->teardown;
    }
}
