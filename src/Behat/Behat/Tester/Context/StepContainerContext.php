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
 * Represents a context for a step container tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface StepContainerContext extends TestContext
{
    /**
     * Creates new step context.
     *
     * @param StepNode $step
     *
     * @return StepContext
     */
    public function createStepContext(StepNode $step);

    /**
     * Returns the feature node.
     *
     * @return FeatureNode
     */
    public function getFeature();

    /**
     * Returns the scenario node.
     *
     * @return ScenarioInterface
     */
    public function getScenario();

    /**
     * Returns the actual step container.
     *
     * @return StepContainerInterface
     */
    public function getContainer();

    /**
     * Returns an array of contained step nodes.
     *
     * @return StepNode[]
     */
    public function getSteps();

    /**
     * Returns the environment.
     *
     * @return Environment
     */
    public function getEnvironment();
}
