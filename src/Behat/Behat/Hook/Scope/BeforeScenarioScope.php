<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Scope;

use Behat\Behat\Tester\Context\ScenarioContext;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;

/**
 * Represents a BeforeScenario hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeScenarioScope implements ScenarioScope
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
     * @var Environment
     */
    private $environment;

    /**
     * Initializes scope.
     *
     * @param ScenarioContext $context
     */
    public function __construct(ScenarioContext $context)
    {
        $this->feature = $context->getFeature();
        $this->scenario = $context->getScenario();
        $this->environment = $context->getEnvironment();
    }

    /**
     * Returns hook scope name.
     *
     * @return string
     */
    public function getName()
    {
        return self::BEFORE;
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
}
