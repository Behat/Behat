<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Context;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Context\TestContext;

/**
 * Represents a context for a step tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StepContext implements TestContext
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
     * @var StepContainerInterface
     */
    private $container;
    /**
     * @var StepNode
     */
    private $step;
    /**
     * @var Environment
     */
    private $environment;

    /**
     * Initializes context.
     *
     * @param StepContainerContext $containerContext
     * @param StepNode             $step
     */
    public function __construct(StepContainerContext $containerContext, StepNode $step)
    {
        $this->feature = $containerContext->getFeature();
        $this->scenario = $containerContext->getScenario();
        $this->container = $containerContext->getContainer();
        $this->step = $step;
        $this->environment = $containerContext->getEnvironment();
    }

    /**
     * Returns the feature node.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Returns the scenario of the step.
     *
     * @return ScenarioInterface
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Returns the container of the step.
     *
     * @return StepContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns the step.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Returns the test environment.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
