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
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;

/**
 * Represents a BeforeStep hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeStepScope implements StepScope
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
     * @var StepNode
     */
    private $step;

    /**
     * Initializes scope.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param StepNode    $step
     */
    public function __construct(Environment $env, FeatureNode $feature, StepNode $step)
    {
        $this->environment = $env;
        $this->feature = $feature;
        $this->step = $step;
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
     * Returns scope step.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }
}
