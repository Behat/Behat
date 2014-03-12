<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Scope;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Scope\AfterTestScope;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an AfterScenario hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterScenarioScope implements ScenarioScope, AfterTestScope
{
    /**
     * @var Environment
     */
    private $environment;
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
     * Initializes scope.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param Scenario    $scenario
     * @param TestResult  $result
     */
    public function __construct(Environment $env, FeatureNode $feature, Scenario $scenario, TestResult $result)
    {
        $this->environment = $env;
        $this->feature = $feature;
        $this->scenario = $scenario;
        $this->result = $result;
    }

    /**
     * Returns hook scope name.
     *
     * @return string
     */
    public function getName()
    {
        return self::AFTER;
    }

    /**
     * Returns hook suite.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->environment->getSuite();
    }

    /**
     * Returns hook environment.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Returns scope feature.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Returns scenario.
     *
     * @return Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Returns test result.
     *
     * @return TestResult
     */
    public function getTestResult()
    {
        return $this->result;
    }
}
