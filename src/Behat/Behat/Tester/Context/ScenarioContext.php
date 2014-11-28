<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Context;

use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;

/**
 * Represents a context for scenario tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ScenarioContext implements StepContainerContext
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
     * Initializes context.
     *
     * @param FeatureNode $feature
     * @param Scenario    $scenario
     * @param Environment $environment
     */
    public function __construct(FeatureNode $feature, Scenario $scenario, Environment $environment)
    {
        $this->feature = $feature;
        $this->scenario = $scenario;
        $this->environment = $environment;
    }

    /**
     * Creates a new context instance with provided environment.
     *
     * @param EnvironmentManager $environmentManager
     *
     * @return ScenarioContext
     */
    public function createIsolatedContext(EnvironmentManager $environmentManager)
    {
        $env = $environmentManager->isolateEnvironment($this->environment, $this->scenario);

        return new ScenarioContext($this->feature, $this->scenario, $env);
    }

    /**
     * Creates a new example context.
     *
     * @param ExampleNode $example
     *
     * @return ScenarioContext
     */
    public function createExampleContext(ExampleNode $example)
    {
        return new ScenarioContext($this->feature, $example, $this->environment);
    }

    /**
     * Creates a new background context.
     *
     * @return BackgroundContext
     */
    public function createBackgroundContext()
    {
        return new BackgroundContext($this);
    }

    /**
     * {@inheritdoc}
     */
    public function createStepContext(StepNode $step)
    {
        return new StepContext($this, $step);
    }

    /**
     * Checks if feature has a background.
     *
     * @return Boolean
     */
    public function hasBackground()
    {
        return $this->feature->hasBackground();
    }

    /**
     * Checks if scenario is an outline.
     *
     * @return Boolean
     */
    public function isOutline()
    {
        return $this->scenario instanceof OutlineNode;
    }

    /**
     * {@inheritdoc}
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Returns background node.
     *
     * @return BackgroundNode
     */
    public function getBackground()
    {
        return $this->feature->getBackground();
    }

    /**
     * {@inheritdoc}
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * {@inheritdoc}
     */
    public function getSteps()
    {
        return $this->scenario->getSteps();
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
