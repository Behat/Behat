<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Call;

use Behat\Behat\Definition\Definition;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Call\EnvironmentCall;
use Behat\Testwork\Environment\Environment;

/**
 * Enhances environment call with definition information.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DefinitionCall extends EnvironmentCall
{
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var StepNode
     */
    private $step;

    /**
     * Initializes definition call.
     *
     * @param Environment  $environment
     * @param FeatureNode  $feature
     * @param StepNode     $step
     * @param Definition   $definition
     * @param array        $arguments
     * @param null|integer $errorReportingLevel
     */
    public function __construct(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        Definition $definition,
        array $arguments,
        $errorReportingLevel = null
    ) {
        parent::__construct($environment, $definition, $arguments, $errorReportingLevel);

        $this->feature = $feature;
        $this->step = $step;
    }

    /**
     * Returns step feature node.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Returns definition step node.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }
}
