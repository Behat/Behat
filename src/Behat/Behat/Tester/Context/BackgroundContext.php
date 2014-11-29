<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Context;

use Behat\Behat\Tester\Exception\FeatureHasNoBackgroundException;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Represents a context for background tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BackgroundContext implements StepContainerContext
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
     * @var Environment
     */
    private $environment;

    /**
     * Initializes context.
     *
     * @param ScenarioContext $scenarioContext
     *
     * @throws FeatureHasNoBackgroundException If feature has no background
     */
    public function __construct(ScenarioContext $scenarioContext)
    {
        $this->feature = $scenarioContext->getFeature();
        $this->scenario = $scenarioContext->getScenario();
        $this->environment = $scenarioContext->getEnvironment();

        if (!$this->feature->hasBackground()) {
            throw new FeatureHasNoBackgroundException(
                sprintf(
                    'Feature `%s` has no background that could be tested.',
                    $this->feature->getFile()
                ), $this->feature
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createStepContext(StepNode $step)
    {
        return new StepContext($this, $step);
    }

    /**
     * {@inheritdoc}
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * {@inheritdoc}
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Returns the background node.
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
    public function getContainer()
    {
        return $this->getBackground();
    }

    /**
     * {@inheritdoc}
     */
    public function getSteps()
    {
        return $this->getBackground()->getSteps();
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
